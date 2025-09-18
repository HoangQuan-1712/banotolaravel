<?php
namespace App\Services;
use App\Models\{Voucher, VoucherUsage, Order, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class VoucherService {
    
    /**
     * TIERED CHOICE: Lấy danh sách voucher theo bậc giá đơn hàng
     * Mục đích: Upsell - khuyến khích khách mua xe đắt hơn để được quà tốt hơn
     */
    public function getTieredChoices(Order $order, User $user): Collection {
        return Voucher::active()
            ->tieredChoice()
            ->where(function($q) use($order) {
                $q->whereNull('min_order_value')
                  ->orWhere('min_order_value', '<=', $order->total_price);
            })
            ->where(function($q) use($order) {
                $q->whereNull('max_order_value')
                  ->orWhere('max_order_value', '>=', $order->total_price);
            })
            ->where(function($q) use($user) {
                // Kiểm tra user chưa sử dụng voucher này
                $q->whereDoesntHave('usages', function($usage) use($user) {
                    $usage->where('user_id', $user->id);
                });
            })
            ->orderBy('min_order_value')
            ->get()
            ->groupBy('group_code'); // Nhóm theo tier
    }

    /**
     * RANDOM GIFT: Chọn ngẫu nhiên 1 voucher từ pool
     * Mục đích: Marketing buzz, tạo cảm giác bất ngờ thú vị
     */
    public function getRandomGift(Order $order, User $user): ?Voucher {
        $pool = Voucher::active()
            ->randomGift()
            ->where(function($q) use($user) {
                $q->whereDoesntHave('usages', function($usage) use($user) {
                    $usage->where('user_id', $user->id);
                });
            })
            ->get();

        if ($pool->isEmpty()) return null;

        // Weighted random selection
        $totalWeight = $pool->sum('weight');
        $randomNumber = rand(1, $totalWeight);
        $currentWeight = 0;

        foreach ($pool as $voucher) {
            $currentWeight += $voucher->weight;
            if ($randomNumber <= $currentWeight) {
                return $voucher;
            }
        }

        return $pool->first(); // Fallback
    }

    /**
     * VIP TIER: Lấy voucher dành riêng cho tier của khách hàng
     * Mục đích: Loyalty program, giữ chân khách hàng lâu dài
     */
    public function getVipTierVouchers(User $user): Collection {
        if (!$user->tier) {
            return collect();
        }

        return Voucher::active()
            ->vipTier($user->tier->level)
            ->where(function($q) use($user) {
                $q->whereDoesntHave('usages', function($usage) use($user) {
                    $usage->where('user_id', $user->id);
                });
            })
            ->get();
    }

    /**
     * Apply voucher vào đơn hàng
     */
    public function applyVoucher(Voucher $voucher, Order $order, User $user): bool {
        if (!$voucher->canBeUsedBy($user, $order->total)) {
            return false;
        }
        // Exclusivity: only one voucher/gift per order
        $alreadyClaimed = $order->items()
                ->where('metadata->type', 'voucher_gift')
                ->exists()
            || $order->adjustments()
                ->where('type', 'voucher_discount')
                ->exists()
            || VoucherUsage::where('order_id', $order->id)->exists();

        if ($alreadyClaimed) {
            return false;
        }

        return DB::transaction(function() use($voucher, $order, $user) {
            // Tạo record usage
            VoucherUsage::create([
                'voucher_id' => $voucher->id,
                'user_id' => $user->id,
                'order_id' => $order->id,
                'used_at' => now(),
            ]);

            // Tăng số lần sử dụng
            $voucher->increment('used_count');
            
            // Giảm stock nếu có
            if ($voucher->stock !== null) {
                $voucher->decrement('stock');
            }

            // Áp dụng voucher vào đơn hàng
            $this->applyVoucherToOrder($voucher, $order);

            return true;
        });
    }

    /**
     * Áp dụng voucher vào đơn hàng cụ thể
     */
    private function applyVoucherToOrder(Voucher $voucher, Order $order): void {
        switch ($voucher->type) {
            case 'tiered_choice':
            case 'random_gift':
            case 'vip_tier':
                // Thêm vào order items như một món quà
                $order->items()->create([
                    'product_id' => null, // Không phải sản phẩm thực
                    'product_name' => $voucher->name,
                    'price' => 0, // Miễn phí
                    'quantity' => 1,
                    'total' => 0,
                    'metadata' => [
                        'type' => 'voucher_gift',
                        'voucher_id' => $voucher->id,
                        'voucher_code' => $voucher->code,
                        'description' => $voucher->description
                    ]
                ]);
                break;

            case 'discount':
                // Tạo adjustment giảm giá
                $discountAmount = min($voucher->value, $order->subtotal);
                $order->adjustments()->create([
                    'type' => 'voucher_discount',
                    'label' => "Voucher: {$voucher->name}",
                    'amount' => -$discountAmount, // Số âm = giảm giá
                    'metadata' => [
                        'voucher_id' => $voucher->id,
                        'voucher_code' => $voucher->code
                    ]
                ]);
                
                // Cập nhật lại total
                $order->recalculateTotal();
                break;

            case 'service_voucher':
                // Thêm dịch vụ miễn phí
                $order->items()->create([
                    'product_id' => null,
                    'product_name' => $voucher->name,
                    'price' => $voucher->value ?? 0,
                    'quantity' => 1,
                    'total' => 0, // Miễn phí nhưng hiển thị giá trị
                    'metadata' => [
                        'type' => 'service_voucher',
                        'voucher_id' => $voucher->id,
                        'original_value' => $voucher->value
                    ]
                ]);
                break;
        }
    }

    /**
     * Lấy tất cả voucher có thể áp dụng cho đơn hàng
     */
    public function getAvailableVouchers(Order $order, User $user): array {
        return [
            'tiered_choices' => $this->getTieredChoices($order, $user),
            'random_gift' => $this->getRandomGift($order, $user),
            'vip_tier' => $this->getVipTierVouchers($user)
        ];
    }
}
