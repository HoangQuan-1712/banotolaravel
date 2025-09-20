<?php
namespace App\Services;
use App\Models\{User, CustomerTier, Order};
use Illuminate\Support\Facades\DB;

class TierService {
    
    /**
     * Cập nhật tier cho user dựa trên tổng chi tiêu
     */
    public function updateUserTier(User $user): ?CustomerTier {
        // Tính tổng chi tiêu từ các đơn hàng đã hoàn thành
        $completedSpent = $user->orders()
            ->where('status', Order::STATUS_COMPLETED)
            ->sum('total_price');

        // Tính tổng tiền cọc từ các đơn hàng đã đặt cọc nhưng chưa hoàn thành
        $depositSpent = $user->orders()
            ->whereIn('status', ['đã đặt cọc (COD)', 'đã đặt cọc (MoMo)'])
            ->sum('deposit_amount');

        $totalSpent = $completedSpent + $depositSpent;

        // Đếm số xe đã mua
        $totalCars = $user->orders()
            ->where('status', Order::STATUS_COMPLETED)
            ->count();

        // Tìm tier phù hợp
        $newTier = CustomerTier::getTierBySpending($totalSpent);
        
        // Cập nhật thông tin user
        $user->update([
            'lifetime_spent' => $totalSpent,
            'total_cars_bought' => $totalCars,
            'tier_id' => $newTier?->id,
            'tier_updated_at' => now()
        ]);

        return $newTier;
    }

    /**
     * Kiểm tra và cập nhật tier sau khi hoàn thành đơn hàng
     */
    public function checkTierUpgrade(User $user, Order $order): array {
        $oldTier = $user->tier;
        $newTier = $this->updateUserTier($user);
        
        $upgraded = false;
        $message = null;

        if (!$oldTier && $newTier) {
            // Lần đầu có tier
            $upgraded = true;
            $message = "Chúc mừng! Bạn đã đạt hạng {$newTier->name}!";
        } elseif ($oldTier && $newTier && $newTier->id !== $oldTier->id) {
            // Nâng cấp tier
            $upgraded = true;
            $message = "Chúc mừng! Bạn đã được nâng cấp từ {$oldTier->name} lên {$newTier->name}!";
        }

        $awardedVouchers = [];
        if ($upgraded && $newTier) {
            // Find all welcome vouchers for the new tier
            $welcomeVouchers = \App\Models\Voucher::where('type', 'vip_tier')
                ->where('tier_level', $newTier->level)
                ->get();

            if ($welcomeVouchers->isNotEmpty()) {
                $awardedVoucherMessages = [];
                foreach ($welcomeVouchers as $voucher) {
                    // Check if the user has already been awarded this specific voucher
                    $alreadyAwarded = \App\Models\VoucherUsage::where('voucher_id', $voucher->id)
                        ->where('user_id', $user->id)
                        ->exists();

                    if (!$alreadyAwarded) {
                        // Award the voucher by creating a usage record with used_at as null
                        \App\Models\VoucherUsage::create([
                            'voucher_id' => $voucher->id,
                            'user_id' => $user->id,
                            'used_at' => null, // Set to null to indicate it's awarded, not used
                        ]);
                        $awardedVouchers[] = $voucher;
                        $awardedVoucherMessages[] = "'{$voucher->name}'";
                    }
                }
                if (!empty($awardedVoucherMessages)) {
                    $message .= " Bạn đã nhận được các voucher: " . implode(', ', $awardedVoucherMessages) . ".";
                }
            }
        }

        return [
            'upgraded' => $upgraded,
            'old_tier' => $oldTier,
            'new_tier' => $newTier,
            'message' => $message,
            'awarded_vouchers' => $awardedVouchers
        ];
    }

    /**
     * Tạo dữ liệu tier mẫu
     */
    public function seedTiers(): void {
        $tiers = [
            [
                'level' => 'bronze',
                'name' => 'Hạng Đồng',
                'min_spent' => 15000, // $15,000
                'benefits' => 'Voucher giảm giá 2% cho lần mua phụ kiện tiếp theo.',
                'color' => '#CD7F32',
                'priority_support' => 1,
                'discount_percentage' => 0
            ],
            [
                'level' => 'silver',
                'name' => 'Hạng Bạc',
                'min_spent' => 50000, // $50,000
                'benefits' => 'Tặng voucher bảo dưỡng miễn phí 1 lần, giảm 5% phí dịch vụ.',
                'color' => '#C0C0C0',
                'priority_support' => 2,
                'discount_percentage' => 5.00
            ],
            [
                'level' => 'gold',
                'name' => 'Hạng Vàng',
                'min_spent' => 120000, // $120,000
                'benefits' => 'Tặng voucher bảo hiểm vật chất trị giá $200, bảo dưỡng miễn phí 1 năm.',
                'color' => '#FFD700',
                'priority_support' => 3,
                'discount_percentage' => 10.00
            ],
            [
                'level' => 'platinum',
                'name' => 'Hạng Kim Cương',
                'min_spent' => 300000, // $300,000
                'benefits' => 'Quà tặng đặc biệt (vd: Gói du lịch), quản lý tài khoản riêng, giảm 15% mọi dịch vụ.',
                'color' => '#b9f2ff',
                'priority_support' => 4,
                'discount_percentage' => 15.00
            ]
        ];

        foreach ($tiers as $tierData) {
            CustomerTier::updateOrCreate(
                ['level' => $tierData['level']], 
                $tierData
            );
        }
    }

    /**
     * Lấy thống kê tier
     */
    public function getTierStats(): array {
        return CustomerTier::withCount('users')
            ->orderBy('min_spent')
            ->get()
            ->map(function($tier) {
                return [
                    'tier' => $tier,
                    'user_count' => $tier->users_count,
                    'percentage' => User::count() > 0 ? 
                        round(($tier->users_count / User::count()) * 100, 1) : 0
                ];
            })
            ->toArray();
    }
}
