<?php
namespace App\Http\Controllers;
use App\Models\{Order, Voucher};
use App\Services\VoucherService;
use Illuminate\Http\Request;

class VoucherController extends Controller {
    
    /**
     * Hiển thị trang chọn voucher cho đơn hàng
     */
    public function showChoices(Order $order, VoucherService $voucherService) {
        $user = auth()->user();
        
        // Kiểm tra quyền truy cập đơn hàng
        if ($order->user_id !== $user->id) {
            abort(403, 'Unauthorized access to order');
        }

        // Lấy tất cả voucher có thể áp dụng
        $vouchers = $voucherService->getAvailableVouchers($order, $user);
        
        return view('checkout.vouchers', [
            'order' => $order,
            'tieredChoices' => $vouchers['tiered_choices'],
            'randomGift' => $vouchers['random_gift'],
            'vipTierVouchers' => $vouchers['vip_tier'],
            'user' => $user
        ]);
    }

    /**
     * Áp dụng voucher được chọn
     */
    public function applyChoice(Request $request, Order $order, VoucherService $voucherService) {
        $request->validate([
            'voucher_id' => 'required|exists:vouchers,id'
        ]);

        $user = auth()->user();
        
        // Kiểm tra quyền truy cập
        if ($order->user_id !== $user->id) {
            abort(403, 'Unauthorized access to order');
        }

        $voucher = Voucher::findOrFail($request->voucher_id);
        
        if ($voucherService->applyVoucher($voucher, $order, $user)) {
            return redirect()
                ->route('user.orders.show', $order)
                ->with('success', "Congratulations! You've received: {$voucher->name}");
        }

        return back()->with('error', 'Unable to apply this voucher. Please check the conditions.');
    }

    /**
     * Nhận quà ngẫu nhiên
     */
    public function randomGift(Request $request, Order $order, VoucherService $voucherService) {
        $user = auth()->user();
        
        // Kiểm tra quyền truy cập
        if ($order->user_id !== $user->id) {
            abort(403, 'Unauthorized access to order');
        }

        $voucher = $voucherService->getRandomGift($order, $user);
        
        if (!$voucher) {
            return back()->with('error', 'No random gifts available at the moment.');
        }

        if ($voucherService->applyVoucher($voucher, $order, $user)) {
            return redirect()
                ->route('user.orders.show', $order)
                ->with('success', "Lucky you! You've received: {$voucher->name} - {$voucher->description}");
        }

        return back()->with('error', 'Unable to process your random gift. Please try again.');
    }

    /**
     * API endpoint để lấy voucher theo AJAX
     */
    public function getAvailableVouchers(Order $order, VoucherService $voucherService) {
        $user = auth()->user();
        
        if ($order->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $vouchers = $voucherService->getAvailableVouchers($order, $user);
        
        return response()->json([
            'tiered_choices' => $vouchers['tiered_choices']->groupBy('group_code'),
            'random_gift_available' => $vouchers['random_gift'] !== null,
            'vip_tier_vouchers' => $vouchers['vip_tier'],
            'user_tier' => $user->tier ? [
                'name' => $user->tier->name,
                'level' => $user->tier->level,
                'benefits' => $user->tier->benefits
            ] : null
        ]);
    }

    /**
     * Preview vouchers for the current cart/amount (no order created yet)
     */
    public function preview(Request $request, VoucherService $voucherService)
    {
        $user = auth()->user();
        // amount can be passed; if not, try from session cart
        $amount = (float) $request->input('amount');
        if ($amount <= 0) {
            $cart = session('cart', []);
            $amount = collect($cart)->sum(fn($i) => ($i['price'] ?? 0) * ($i['quantity'] ?? 1));
        }

        // Build a lightweight result similar to getAvailableVouchers but by amount
        $tiered = \App\Models\Voucher::active()
            ->tieredChoice()
            ->where(function($q) use($amount) {
                $q->whereNull('min_order_value')
                  ->orWhere('min_order_value', '<=', $amount);
            })
            ->where(function($q) use($amount) {
                $q->whereNull('max_order_value')
                  ->orWhere('max_order_value', '>=', $amount);
            })
            ->whereDoesntHave('usages', function($u) use($user) { $u->where('user_id', $user->id); })
            ->get()
            ->groupBy('group_code');

        $randomGiftAvailable = \App\Models\Voucher::active()
            ->randomGift()
            ->whereDoesntHave('usages', function($u) use($user) { $u->where('user_id', $user->id); })
            ->exists();

        $vip = $user->tier ? \App\Models\Voucher::active()->vipTier($user->tier->level)
            ->whereDoesntHave('usages', function($u) use($user) { $u->where('user_id', $user->id); })
            ->get() : collect();

        return response()->json([
            'amount' => $amount,
            'tiered_choices' => $tiered,
            'random_gift_available' => $randomGiftAvailable,
            'vip_tier_vouchers' => $vip,
        ]);
    }

    /**
     * Preview applying a specific voucher to a given amount (no order yet)
     */
    public function previewApply(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|exists:vouchers,id',
            'amount' => 'required|numeric|min:0'
        ]);

        $user = auth()->user();
        $amount = (float) $request->input('amount');

        $voucher = \App\Models\Voucher::findOrFail($request->voucher_id);

        if (!$voucher->canBeUsedBy($user, $amount)) {
            return response()->json([
                'ok' => false,
                'message' => 'Voucher không đủ điều kiện áp dụng cho giá trị hiện tại.'
            ], 422);
        }

        // Calculate discount if applicable
        $discount = 0.0;
        if ($voucher->type === 'discount') {
            $discount = min((float) ($voucher->value ?? 0), $amount);
        }

        $newTotal = max(0, $amount - $discount);
        $newDeposit = $newTotal * 0.3; // 30%

        return response()->json([
            'ok' => true,
            'voucher' => [
                'id' => $voucher->id,
                'name' => $voucher->name,
                'description' => $voucher->description,
                'type' => $voucher->type,
                'value' => $voucher->value,
            ],
            'discount' => $discount,
            'new_total' => $newTotal,
            'new_deposit' => $newDeposit,
        ]);
    }
}
