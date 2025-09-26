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
        // Nếu có order_id, nghĩa là đang thanh toán lại cho đơn hàng cũ
        if ($request->has('order_id')) {
            $existingOrder = Order::findOrFail($request->order_id);
            if ($existingOrder->user_id !== Auth::id()) {
                abort(403, 'Unauthorized');
            }
            // Cập nhật thông tin và tiếp tục thanh toán cho đơn hàng cũ
            return $this->proceedToPayment($request, $existingOrder);
        }
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

        // Tạo đơn hàng mới nếu không có order_id
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

        // Áp dụng voucher nếu người dùng đã chọn trong trang đặt cọc
        if ($request->filled('selected_voucher_id')) {
            try {
                $voucher = \App\Models\Voucher::find($request->input('selected_voucher_id'));
                if ($voucher && $voucher->canBeUsedBy(auth()->user(), $total)) {
                    if ($voucher->type === 'discount') {
                        // Tính giảm giá và cập nhật tổng tiền đơn + tiền cọc
                        $discount = min((float) ($voucher->value ?? 0), $order->total_price);
                        if ($discount > 0) {
                            $order->update([
                                'total_price' => max(0, $order->total_price - $discount),
                                'deposit_amount' => max(0, ($order->total_price - $discount) * 0.3),
                            ]);
                            // Ghi nhận sử dụng voucher
                            \App\Models\VoucherUsage::create([
                                'voucher_id' => $voucher->id,
                                'user_id' => auth()->id(),
                                'order_id' => $order->id,
                                'used_at' => now(),
                            ]);
                            $voucher->increment('used_count');
                            if (!is_null($voucher->stock)) { $voucher->decrement('stock'); }
                            session()->flash('voucher_applied_message', 'Đã áp dụng voucher giảm ' . number_format($discount, 0, ',', '.') . ' đ.');
                        }
                    } else {
                        // Áp dụng như quà tặng (tiered/random/vip)
                        app(\App\Services\VoucherService::class)->applyVoucher($voucher, $order, auth()->user());
                        session()->flash('voucher_applied_message', 'Đã thêm quà tặng: ' . $voucher->name);
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Apply voucher on payment failed', ['error' => $e->getMessage()]);
            }
        }

        // Nếu người dùng bấm nút "Chọn voucher/Quà tặng", luôn điều hướng sang trang chọn ưu đãi
        if ($request->boolean('preview_voucher')) {
            session()->forget('cart');
            return redirect()->route('vouchers.choices', $order);
        }

        // Nếu không xem voucher, thì đi thẳng đến thanh toán
        session()->forget('cart');

        // ✅ XÓA GIỎ HÀNG NGAY KHI NHẤN THANH TOÁN (kể cả MoMo chưa thành công)
        session()->forget('cart');

        return $this->proceedToPayment($request, $order);
    }

    protected function proceedToPayment(Request $request, Order $order)
    {
        $deposit = $order->total_price * 0.3;

        // Rẽ nhánh phương thức
        if ($request->payment_method === 'momo') {
            return $this->redirectToMoMo($order);
        }

        // COD - reserve stock immediately on COD deposit
        $order->update([
            'status' => 'đã đặt cọc (COD)',
        ]);

        // Trigger tier update for COD orders as well
        $tierUpgradeResult = app(TierService::class)->checkTierUpgrade($order->user, $order);
        if ($tierUpgradeResult['upgraded']) {
            // Use a different session key to avoid being overwritten
            session()->flash('tier_upgrade_message', $tierUpgradeResult['message']);
        }

        // Reserve inventory for COD deposit
        try {
            $order->reserveStock();
        } catch (\Throwable $e) {
            \Log::error('Reserve stock failed for COD order ' . $order->id . ': ' . $e->getMessage());
        }

        $successMessage = 'Đặt cọc thành công! Số tiền cọc: ' . number_format($deposit, 0, ',', '.') . ' $. Số tiền còn lại sẽ thanh toán khi nhận xe.';
        if (session('tier_upgrade_message')) {
            $successMessage .= "\n" . session('tier_upgrade_message');
        }
        return redirect()->route('user.orders.index')->with('success', $successMessage);
    }

    /**
     * Tạo giao dịch MoMo và chuyển hướng người dùng
     */
    protected function redirectToMoMo(Order $order)
    {
        $redirectUrl = route('user.payment.momo.callback');
        $ipnUrl = route('user.payment.momo.ipn');
        $orderId = time() . '_' . $order->id;
        $requestId = uniqid();

        $orderInfo = "Thanh toán đơn hàng #{$order->id}";

        // 1) Số tiền thanh toán = Tổng tiền đơn hàng (đơn vị VND)
        // MoMo yêu cầu số nguyên VND, tối thiểu 1,000 VND với payWithATM
        $orderTotalVND = (int) ceil($order->total_price);
        $amount = (string) max(1000, $orderTotalVND);

        // 2) Dùng payWithATM để hiển thị trang NAPAS như ảnh demo
        $requestType = 'payWithATM';

        // 3) KÝ ĐÚNG THỨ TỰ THAM SỐ
        $extraData = base64_encode(json_encode(['order_id' => $order->id, 'user_id' => $order->user_id]));
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
// 4) GỬI DẠNG JSON
$response = Http::withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
->asJson()
->withoutVerifying()
->post($this->endpoint, $payload);

if (!$response->successful()) {
Log::error('MoMo create payment failed', [
'status' => $response->status(),
'body' => $response->body(),
]);
return redirect()
->route('user.payment.index')
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

$msg = $json['message'] ?? ($json['errorMessage'] ?? 'MoMo không trả về payUrl.');
Log::error('MoMo payUrl missing', ['response' => $json]);

return redirect()
->route('user.payment.index')
->with('error', 'Không tạo được link thanh toán MoMo: ' . $msg);

} catch (\Exception $e) {
Log::error('MoMo request exception', ['error' => $e->getMessage()]);
return redirect()
->route('user.payment.index')
->with('error', 'Lỗi khi tạo thanh toán MoMo: ' . $e->getMessage());
}
}

    /**
     * Callback: người dùng được MoMo chuyển về sau thanh toán
     */
    public function callback(Request $request)
    {
        $resultCode = $request->input('resultCode'); // 0 = success
        $message = $request->input('message') ?? $request->input('localMessage');

        // Lấy order từ orderId (định dạng time_id)
        $order = null;
        if ($request->filled('orderId')) {
            $parts = explode('_', $request->orderId);
            $orderId = end($parts);
            $order = Order::find($orderId);
        }

        if ((string) $resultCode === '0') {
            // ✅ Thành công
            session()->forget('cart');
            if ($order) {
                $order->update(['status' => 'đã đặt cọc (MoMo)']);
                try { $order->reserveStock(); } catch (\Throwable $e) { \Log::error('Reserve stock failed for MoMo order ' . ($order->id ?? 'N/A') . ': ' . $e->getMessage()); }
                $tierUpgradeResult = app(TierService::class)->checkTierUpgrade($order->user, $order);
                if ($tierUpgradeResult['upgraded']) {
                    session()->flash('tier_upgrade_message', $tierUpgradeResult['message']);
                }
            }
            $deposit = $order ? ($order->total_price * 0.3) : 0;
            $successMessage = 'Đặt cọc MoMo thành công! Số tiền cọc: ' . number_format($deposit, 0, ',', '.') . ' $';
            if (session('tier_upgrade_message')) { $successMessage .= "\n" . session('tier_upgrade_message'); }
            return redirect()->route('user.orders.index')->with('success', $successMessage);
        }

        // ❌ Thất bại/hủy
        if ($order) {
            $order->update(['status' => 'thanh toán MoMo không thành công']);
            try { $order->releaseReservedStock(); } catch (\Throwable $e) { \Log::error('Release stock failed on MoMo fail for order ' . $order->id . ': ' . $e->getMessage()); }
        }

        $errorText = 'Thanh toán MoMo thất bại hoặc bị hủy.' . ($message ? ' (' . $message . ')' : '');
        return redirect()->route('user.payment.index')->with('error', $errorText);
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

        $payableStatuses = [
            'chờ đặt cọc',
            'thanh toán MoMo không thành công',
            'chờ thanh toán',
            Order::STATUS_AWAITING_DEPOSIT ?? 'chờ đặt cọc',
        ];

        if (!in_array($order->status, $payableStatuses, true)) {
            return redirect()->route('user.orders.index')->with('info', 'Đơn hàng này không cần thanh toán lại.');
        }

        // Tái tạo giỏ hàng từ order items để truyền vào trang thanh toán
        $cart = [];
        foreach ($order->items as $item) {
            if ($item->product) { // Ensure product exists
                $cart[$item->product_id] = [
                    'id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'image' => $item->product->image,
                    'category' => optional($item->product->category)->name ?? 'Sản phẩm',
                    'max_quantity' => $item->product->quantity
                ];
            }
        }
        session(['cart' => $cart]);

        // Chuyển hướng đến trang đặt cọc/thanh toán, mang theo ID của đơn hàng cũ
        return redirect()->route('user.payment.index', ['order_id' => $order->id]);
    }

    /**
     * Cancel an order that is still waiting for deposit/payment.
     */
    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền hủy đơn này.');
        }

        // Allow cancel only for waiting/failed statuses
        $cancelableStatuses = [
            'chờ đặt cọc',
            'chờ thanh toán',
            'thanh toán MoMo không thành công',
            Order::STATUS_AWAITING_DEPOSIT ?? 'chờ đặt cọc',
        ];

        if (!in_array($order->status, $cancelableStatuses, true)) {
            return redirect()->route('user.orders.index')
                ->with('error', 'Đơn hàng hiện tại không thể hủy.');
        }

        // Release reserved stock if any
        try {
            if (method_exists($order, 'releaseReservedStock')) {
                $order->releaseReservedStock();
            }
        } catch (\Throwable $e) {
            \Log::error('Release stock failed on cancel for order ' . $order->id . ': ' . $e->getMessage());
        }

        $order->update(['status' => 'đã hủy']);

        return redirect()->route('user.orders.index')
            ->with('success', 'Đơn hàng #' . $order->id . ' đã được hủy.');
    }

    // gọi lịch sử các đơn hàng theo người dùng
    public function orderHistory()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product', 'reviews', 'voucherUsages.voucher'])
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
        $order = Order::with(['items.product', 'voucherUsages.voucher'])
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

        // Create or update review uniquely by (user_id, product_id) to honor DB unique index
        $userId = auth()->id();
        $productId = (int) $request->product_id;

        $review = DB::transaction(function () use ($userId, $productId, $request) {
            // Lock existing row (if any) to avoid race condition
            $existing = Review::where('user_id', $userId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            $payload = [
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
                'is_verified_purchase' => true,
                'status' => Review::STATUS_APPROVED,
            ];

            if ($existing) {
                // Keep order_id if already set, otherwise attach this order
                if (empty($existing->order_id)) {
                    $existing->order_id = $request->order_id;
                }
                $existing->fill($payload)->save();
                return $existing->fresh();
            }

            return Review::create(array_merge([
                'user_id' => $userId,
                'product_id' => $productId,
                'order_id' => $request->order_id,
            ], $payload));
        }, 3);

        $message = ($review->wasRecentlyCreated ?? false)
            ? 'Đánh giá của bạn đã được gửi thành công!'
            : 'Đánh giá của bạn đã được cập nhật!';

        return redirect()->back()->with('success', $message);
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
