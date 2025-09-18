<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{Order, Product, Category, ProductImage};
use App\Services\{MoMoService, VoucherService, TierService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log, DB};
use Illuminate\Support\Facades\Http;
use App\Models\OrderItem;
use App\Models\UserAddress;
use App\Models\Review;

class OrderController extends Controller
{
    // ==========================
    // Đặt cấu hình thông số MoMo test
    // ==========================
    private $endpoint = 'https://test-payment.momo.vn/v2/gateway/api/create';
    private $partnerCode = 'MOMOBKUN20180529';
    private $accessKey = 'klm05TvNBzhg7h7j';
    private $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';

    // giới hạn thao tác: nếu có sản phẩm => truyền đến trang checkout, nếu không có => ở lại giỏ hàng
    public function index()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        // Tính tổng tiền để hiển thị trong form
        $total = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

        // Lấy danh sách địa chỉ của user
        $addresses = Auth::user()->addresses()->orderBy('is_default', 'desc')->get();
        $defaultAddress = Auth::user()->defaultAddress;

        return view('user.payment.index', compact('cart', 'total', 'addresses', 'defaultAddress'));
    }

    // nhận thao tác thanh toán từ form rồi điều hướng kết quả momo hay COD
    public function processPayment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'total_price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cod,momo',
            'address_id' => 'nullable|exists:user_addresses,id',
            'use_saved_address' => 'boolean',
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Không thể thanh toán vì giỏ hàng trống.');
        }

        // Tính tổng tiền và tiền cọc (30%)
        $total = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $deposit = $total * 0.3; // 30% cọc

        // Xử lý địa chỉ - ưu tiên địa chỉ đã lưu nếu được chọn
        $name = $request->name;
        $address = $request->address;
        $phone = $request->phone;

        if ($request->use_saved_address && $request->address_id) {
            $savedAddress = auth()->user()->addresses()->find($request->address_id);
            if ($savedAddress) {
                $name = $savedAddress->name;
                $address = $savedAddress->full_address;
                $phone = $savedAddress->phone;
            }
        }

        // Tạo đơn
        $order = Order::create([
            'user_id' => Auth::id(),
            'name' => $name,
            'address' => $address,
            'phone' => $phone,
            'total_price' => $total,
            'deposit_amount' => $deposit,
            'status' => Order::STATUS_AWAITING_DEPOSIT,
        ]);

        // Lưu chi tiết đơn
        foreach ($cart as $key => $item) {
            // Xử lý cả trường hợp cũ (không có id) và mới (có id)
            $productId = $item['id'] ?? $item['product_id'] ?? $key;

            if ($productId) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
        }

        // Nếu người dùng bấm nút "Chọn voucher/Quà tặng" thì luôn điều hướng sang trang chọn ưu đãi
        if ($request->boolean('preview_voucher')) {
            // Xoá giỏ và chuyển đến trang chọn voucher (nếu không có sẽ hiển thị thông báo không có ưu đãi)
            session()->forget('cart');
            return redirect()->route('vouchers.choices', $order)
                ->with('info', 'Hãy xem ưu đãi khả dụng cho đơn hàng của bạn');
        }

        // Trước khi điều hướng thanh toán: nếu có ưu đãi khả dụng thì điều hướng sang trang chọn voucher/quà tặng
        try {
            $voucherService = new VoucherService();
            $available = $voucherService->getAvailableVouchers($order, auth()->user());
            $hasVouchers = ($available['tiered_choices']->isNotEmpty())
                || ($available['random_gift'] !== null)
                || ($available['vip_tier']->isNotEmpty());

            if ($hasVouchers) {
                // Xoá giỏ trước khi rẽ sang trang voucher
                session()->forget('cart');
                return redirect()->route('vouchers.choices', $order)
                    ->with('success', 'Chọn voucher/quà tặng dành riêng cho bạn trước khi thanh toán.');
            }
        } catch (\Throwable $e) {
            \Log::error('Check vouchers failed for order ' . $order->id . ': ' . $e->getMessage());
        }

        // ✅ XÓA GIỎ HÀNG NGAY KHI NHẤN THANH TOÁN (kể cả MoMo chưa thành công)
        session()->forget('cart');

        // Rẽ nhánh phương thức
        if ($request->payment_method === 'momo') {
            return $this->redirectToMoMo($order, $deposit);
        }

        // COD - reserve stock immediately on COD deposit
        $order->update([
            'status' => 'đã đặt cọc (COD)',
        ]);

        // Reserve inventory for COD deposit
        try {
            $order->reserveStock();
        } catch (\Throwable $e) {
            \Log::error('Reserve stock failed for COD order ' . $order->id . ': ' . $e->getMessage());
        }

        return redirect()->route('user.orders.index')
            ->with('success', 'Đặt cọc thành công! Số tiền cọc: ' . number_format($deposit, 0, ',', '.') . ' $. Số tiền còn lại sẽ thanh toán khi nhận xe.');
    }

    /**
     * Tạo giao dịch MoMo và chuyển hướng người dùng
     */
    protected function redirectToMoMo(Order $order, $deposit = null)
    {
        $redirectUrl = route('user.payment.momo.callback');
        $ipnUrl = route('user.payment.momo.ipn');
        $orderId = time() . '_' . $order->id;
        $requestId = uniqid();

        $orderInfo = "Đặt cọc đơn hàng #{$order->id}";
        $amount = (string) max(1000, (int) ($deposit ?? $order->total_price)); // Sử dụng tiền cọc nếu có
        $extraData = ''; // có thể base64_encode(json_encode(...))
        $requestType = 'payWithATM';
        $rawHash = "accessKey={$this->accessKey}&amount={$amount}&extraData={$extraData}&ipnUrl={$ipnUrl}"
            . "&orderId={$orderId}&orderInfo={$orderInfo}&partnerCode={$this->partnerCode}"
            . "&redirectUrl={$redirectUrl}&requestId={$requestId}&requestType={$requestType}";
        $signature = hash_hmac('sha256', $rawHash, $this->secretKey);

        $payload = [
            'partnerCode' => $this->partnerCode,
            'partnerName' => "YourStore",
            'storeId' => "Store_01",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature,
        ];

        Log::info('MoMo request payload: ', $payload);

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
                ->withoutVerifying()
                ->post($this->endpoint, $payload);

            if (!$response->successful()) {
                Log::error('MoMo create payment failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return redirect()
                    ->route('user.orders.index')
                    ->with('error', 'Không thể kết nối MoMo (' . $response->status() . '). Vui lòng thử lại.');
            }

            $json = $response->json();
            Log::info('MoMo response:', $json);

            if (!empty($json['payUrl'])) {
                $order->update([
                    'momo_request_id' => $requestId,
                    'momo_order_id' => $orderId,
                ]);
                return redirect()->away($json['payUrl']);
            }

            // Không có payUrl → báo lỗi rõ
            $msg = $json['message'] ?? 'MoMo không trả về payUrl.';
            Log::error('MoMo payUrl missing', ['response' => $json]);
            return redirect()
                ->route('user.orders.index')
                ->with('error', 'Không tạo được link thanh toán MoMo: ' . $msg);
        } catch (\Exception $e) {
            Log::error('MoMo request exception', ['error' => $e->getMessage()]);
            return redirect()
                ->route('user.orders.index')
                ->with('error', 'Lỗi khi tạo thanh toán MoMo: ' . $e->getMessage());
        }
    }

    /**
     * Callback: người dùng được MoMo chuyển về sau thanh toán
     */
    public function callback(Request $request)
    {
        $resultCode = $request->input('resultCode'); // 0 = success

        // Có orderId thì lấy id thực từ "time_orderId"
        $order = null;
        if ($request->filled('orderId')) {
            $parts = explode('_', $request->orderId);
            $orderId = end($parts);
            $order = Order::find($orderId);
        }

        if ($resultCode === '0' || $resultCode === 0) {
            // ✅ Thành công: xoá giỏ + cập nhật trạng thái đơn
            session()->forget('cart');
            if ($order) {
                $order->update(['status' => 'đã đặt cọc (MoMo)']);
                // Reserve inventory for successful MoMo deposit
                try {
                    $order->reserveStock();
                } catch (\Throwable $e) {
                    \Log::error('Reserve stock failed for MoMo order ' . $order->id . ': ' . $e->getMessage());
                }
                
                // Process vouchers and tier upgrade after successful payment
                $this->processOrderCompletion($order);
            }
            return redirect()->route('user.orders.index')
                ->with('success', 'Đặt cọc MoMo thành công! Số tiền cọc: ' . number_format($order->total_price * 0.3, 0, ',', '.') . ' $');
        }

        // ❌ Thất bại/hủy: giữ nguyên giỏ hàng để user thử lại
        if ($order) {
            $order->update(['status' => 'thanh toán MoMo không thành công']);
            // Release reserved stock on failed payment
            try {
                $order->releaseReservedStock();
            } catch (\Throwable $e) {
                \Log::error('Release stock failed on MoMo fail for order ' . $order->id . ': ' . $e->getMessage());
            }
        }

        // Quay lại trang checkout để người dùng thử thanh toán lại
        return redirect()->route('user.payment.index')
            ->with('error', 'Thanh toán MoMo thất bại hoặc bị hủy. Vui lòng thử lại.');
    }

    /**
     * IPN: MoMo gọi ngầm (server-to-server) báo trạng thái
     */
    public function ipn(Request $request)
    {
        Log::info('MoMo IPN payload:', $request->all());

        // TODO: bạn nên xác thực chữ ký ở đây
        // Ví dụ cập nhật trạng thái dựa vào orderId/resultCode:
        if ($request->filled('orderId')) {
            $parts = explode('_', $request->orderId);
            $orderId = end($parts);
            if ($order = Order::find($orderId)) {
                if ((string)($request->resultCode) === '0') {
                    $order->update(['status' => 'đã thanh toán (MoMo)']);
                } else {
                    $order->update(['status' => 'thanh toán thất bại (MoMo)']);
                }
            }
        }

        return response()->json(['resultCode' => 0, 'message' => 'Received']);
    }

    // Cho phép user kéo lại đơn chưa thanh toán đi MoMo lần nữa
    public function payAgain(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền thanh toán lại đơn này.');
        }

        if ($order->status === 'đã thanh toán (MoMo)') {
            return redirect()->route('user.orders.index')->with('info', 'Đơn này đã thanh toán.');
        }

        // Đưa về "chờ thanh toán" trước khi tạo giao dịch mới (tuỳ bạn)
        $order->update(['status' => 'chờ thanh toán']);

        // PHẢI return
        return $this->redirectToMoMo($order);
    }

    // gọi lịch sử các đơn hàng theo người dùng
    public function orderHistory()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product', 'reviews'])
            ->orderByDesc('created_at')
            ->paginate(10);
        return view('user.orders.index', compact('orders'));
    }

    // gọi chi tiết sản phẩm từng đơn hàng (legacy method - kept for backward compatibility)
    public function showPaymentOrder(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập đơn hàng này.');
        }
        $order->load('items.product');
        return view('user.payment.show', compact('order'));
    }


    // Chi tiết 1 đơn hàng (có items & product) + map reviewedPairs để tránh N+1
    public function show(Request $request, $orderId)
    {
        // Eager load items.product để tránh N+1 products
        $order = Order::with(['items.product'])
            ->where('id', $orderId)
            ->where('user_id', $request->user()->id) // đảm bảo là đơn của chính user
            ->firstOrFail();

        // -------- Helper tránh N+1 (đánh giá đã tồn tại) --------
        // Lấy list product_id trong đơn:
        $productIds = $order->items->pluck('product_id')->unique()->values();

        // Query duy nhất xem user đã review những product nào trong đơn này
        // -> trả về map dạng [product_id => true]
        $reviewedPairs = Review::where('order_id', $order->id)
            ->where('user_id', $request->user()->id)
            ->whereIn('product_id', $productIds)
            ->pluck('product_id')
            ->flip(); // key là product_id đã review (đỡ N+1 ở view)

        return view('user.orders.show', compact('order', 'reviewedPairs'));
    }

    // Store review from order history
    public function storeReview(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'comment' => 'required|string|max:1000',
        ]);

        // Verify user owns the order
        $order = Order::where('id', $request->order_id)
                     ->where('user_id', auth()->id())
                     ->first();
        
        if (!$order) {
            return redirect()->back()->with('error', 'Đơn hàng không tồn tại hoặc không thuộc về bạn.');
        }

        // Verify order contains the product
        $orderItem = $order->items()->where('product_id', $request->product_id)->first();
        if (!$orderItem) {
            return redirect()->back()->with('error', 'Sản phẩm không có trong đơn hàng này.');
        }

        // Check if already reviewed
        $existingReview = Review::where([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'order_id' => $request->order_id,
        ])->first();

        if ($existingReview) {
            return redirect()->back()->with('error', 'Bạn đã đánh giá sản phẩm này rồi.');
        }

        // Create review
        Review::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
            'is_verified_purchase' => true,
            'status' => 'approved', // Auto approve for verified purchases
        ]);

        return redirect()->back()->with('success', 'Đánh giá của bạn đã được gửi thành công!');
    }

    /**
     * Process order completion - handle tier upgrades and voucher eligibility
     */
    private function processOrderCompletion(Order $order)
    {
        try {
            $user = $order->user;
            $tierService = new TierService();
            
            // Check for tier upgrade
            $tierUpgrade = $tierService->checkTierUpgrade($user, $order);
            
            if ($tierUpgrade['upgraded']) {
                // Store tier upgrade message in session
                session()->flash('tier_upgrade', $tierUpgrade['message']);
                
                // Log tier upgrade
                Log::info("User {$user->id} upgraded tier", [
                    'old_tier' => $tierUpgrade['old_tier']?->name,
                    'new_tier' => $tierUpgrade['new_tier']?->name,
                    'order_id' => $order->id,
                    'total_spent' => $user->lifetime_spent
                ]);
            }
            
            // Check for available vouchers
            $voucherService = new VoucherService();
            $availableVouchers = $voucherService->getAvailableVouchers($order, $user);
            
            $hasVouchers = $availableVouchers['tiered_choices']->isNotEmpty() || 
                          $availableVouchers['random_gift'] !== null || 
                          $availableVouchers['vip_tier']->isNotEmpty();
            
            if ($hasVouchers) {
                // Store voucher availability in session
                session()->flash('vouchers_available', true);
                session()->flash('order_id_for_vouchers', $order->id);
            }
            
        } catch (\Exception $e) {
            Log::error('Error processing order completion', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show voucher selection page after order completion
     */
    public function showVouchers(Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to order');
        }

        // Redirect to voucher controller
        return redirect()->route('vouchers.choices', $order);
    }

    /**
     * Mark order as completed (for admin or after full payment)
     */
    public function markAsCompleted(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        DB::transaction(function() use ($order) {
            $order->update(['status' => Order::STATUS_COMPLETED]);
            $this->processOrderCompletion($order);
        });

        $message = 'Order completed successfully!';
        
        if (session()->has('tier_upgrade')) {
            $message .= ' ' . session()->get('tier_upgrade');
        }
        
        if (session()->has('vouchers_available')) {
            return redirect()->route('vouchers.choices', $order)
                ->with('success', $message . ' Check out your exclusive rewards!');
        }

        return redirect()->route('user.orders.show', $order)
            ->with('success', $message);
    }
}
