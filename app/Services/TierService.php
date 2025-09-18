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
        $totalSpent = $user->orders()
            ->where('status', Order::STATUS_COMPLETED)
            ->sum('total');

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

        return [
            'upgraded' => $upgraded,
            'old_tier' => $oldTier,
            'new_tier' => $newTier,
            'message' => $message
        ];
    }

    /**
     * Tạo dữ liệu tier mẫu
     */
    public function seedTiers(): void {
        $tiers = [
            [
                'level' => 'bronze',
                'name' => 'Bronze Member',
                'min_spent' => 0,
                'benefits' => 'Ưu đãi cơ bản: Tư vấn miễn phí, hỗ trợ kỹ thuật 24/7',
                'color' => '#CD7F32',
                'priority_support' => 1,
                'discount_percentage' => 0
            ],
            [
                'level' => 'silver',
                'name' => 'Silver VIP',
                'min_spent' => 50000, // $50k - mua 1 xe cỡ trung
                'benefits' => 'Ưu đãi Silver: Giảm 5% phí dịch vụ, bảo dưỡng miễn phí 6 tháng, ưu tiên hỗ trợ',
                'color' => '#C0C0C0',
                'priority_support' => 2,
                'discount_percentage' => 5.00
            ],
            [
                'level' => 'gold',
                'name' => 'Gold Elite',
                'min_spent' => 150000, // $150k - mua xe cao cấp hoặc 2-3 xe
                'benefits' => 'Ưu đãi Gold: Giảm 10% phí dịch vụ, bảo dưỡng miễn phí 1 năm, voucher bảo hiểm, tư vấn VIP',
                'color' => '#FFD700',
                'priority_support' => 3,
                'discount_percentage' => 10.00
            ],
            [
                'level' => 'platinum',
                'name' => 'Platinum Exclusive',
                'min_spent' => 500000, // $500k - khách hàng doanh nghiệp hoặc siêu giàu
                'benefits' => 'Ưu đãi Platinum: Giảm 15% phí dịch vụ, bảo dưỡng miễn phí 2 năm, voucher du lịch VIP, quản lý tài khoản riêng',
                'color' => '#E5E4E2',
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
