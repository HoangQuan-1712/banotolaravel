<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Voucher;
use App\Services\TierService;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        // First seed the tiers
        $tierService = new TierService();
        $tierService->seedTiers();

        // TIERED CHOICE VOUCHERS - Upsell strategy
        $tieredChoiceVouchers = [
            // Mid-range tier ($50K - $100K)
            [
                'code' => 'MIDRANGE_TINT',
                'type' => 'tiered_choice',
                'name' => 'Premium Window Tinting',
                'description' => 'Professional ceramic window tinting for your new car. UV protection and enhanced privacy.',
                'value' => 1500.00,
                'group_code' => 'midrange_50k_100k',
                'min_order_value' => 50000,
                'max_order_value' => 100000,
                'applicable_categories' => ['sedan', 'suv', 'hatchback'],
                'metadata' => ['duration' => '1 year warranty', 'service_type' => 'installation']
            ],
            [
                'code' => 'MIDRANGE_DASHCAM',
                'type' => 'tiered_choice',
                'name' => 'HD Dash Camera System',
                'description' => 'Front and rear HD dash camera with night vision and parking mode.',
                'value' => 800.00,
                'group_code' => 'midrange_50k_100k',
                'min_order_value' => 50000,
                'max_order_value' => 100000,
                'applicable_categories' => ['sedan', 'suv', 'hatchback'],
                'metadata' => ['duration' => '2 year warranty', 'service_type' => 'installation']
            ],
            [
                'code' => 'MIDRANGE_MAINTENANCE',
                'type' => 'tiered_choice',
                'name' => '1-Year Maintenance Package',
                'description' => 'Free oil changes, tire rotation, and basic maintenance for 1 year.',
                'value' => 2000.00,
                'group_code' => 'midrange_50k_100k',
                'min_order_value' => 50000,
                'max_order_value' => 100000,
                'applicable_categories' => ['sedan', 'suv', 'hatchback'],
                'metadata' => ['duration' => '12 months', 'service_type' => 'maintenance']
            ],

            // Luxury tier ($100K+)
            [
                'code' => 'LUXURY_INSURANCE',
                'type' => 'tiered_choice',
                'name' => 'Comprehensive Insurance Voucher',
                'description' => '$5,000 voucher towards comprehensive car insurance coverage.',
                'value' => 5000.00,
                'group_code' => 'luxury_100k_plus',
                'min_order_value' => 100000,
                'max_order_value' => null,
                'applicable_categories' => ['luxury', 'sports', 'suv'],
                'metadata' => ['duration' => '1 year coverage', 'service_type' => 'insurance']
            ],
            [
                'code' => 'LUXURY_AUDIO',
                'type' => 'tiered_choice',
                'name' => 'Premium Audio System',
                'description' => 'High-end audio system upgrade with premium speakers and amplifier.',
                'value' => 3500.00,
                'group_code' => 'luxury_100k_plus',
                'min_order_value' => 100000,
                'max_order_value' => null,
                'applicable_categories' => ['luxury', 'sports', 'suv'],
                'metadata' => ['duration' => '3 year warranty', 'service_type' => 'installation']
            ],
            [
                'code' => 'LUXURY_CERAMIC',
                'type' => 'tiered_choice',
                'name' => 'Ceramic Paint Protection',
                'description' => 'Professional ceramic coating for ultimate paint protection and shine.',
                'value' => 4000.00,
                'group_code' => 'luxury_100k_plus',
                'min_order_value' => 100000,
                'max_order_value' => null,
                'applicable_categories' => ['luxury', 'sports', 'suv'],
                'metadata' => ['duration' => '5 year warranty', 'service_type' => 'detailing']
            ]
        ];

        // RANDOM GIFT VOUCHERS - Marketing buzz
        $randomGiftVouchers = [
            [
                'code' => 'RANDOM_GAS_5K',
                'type' => 'random_gift',
                'name' => '$5,000 Gas Voucher',
                'description' => 'Free gas voucher worth $5,000. Use at any participating gas station.',
                'value' => 5000.00,
                'weight' => 10, // 10% chance
                'stock' => 50,
                'metadata' => ['validity' => '1 year', 'stations' => 'Shell, Exxon, BP']
            ],
            [
                'code' => 'RANDOM_MAINTENANCE_FREE',
                'type' => 'random_gift',
                'name' => 'Free Maintenance Package',
                'description' => '6 months of free basic maintenance including oil changes and inspections.',
                'value' => 1200.00,
                'weight' => 25, // 25% chance
                'stock' => 100,
                'metadata' => ['duration' => '6 months', 'service_type' => 'maintenance']
            ],
            [
                'code' => 'RANDOM_ACCESSORIES',
                'type' => 'random_gift',
                'name' => 'Premium Accessories Kit',
                'description' => 'Floor mats, seat covers, steering wheel cover, and air fresheners.',
                'value' => 500.00,
                'weight' => 40, // 40% chance
                'stock' => 200,
                'metadata' => ['items' => 'floor_mats,seat_covers,steering_cover,air_freshener']
            ],
            [
                'code' => 'RANDOM_DETAILING',
                'type' => 'random_gift',
                'name' => 'Professional Car Detailing',
                'description' => 'Complete interior and exterior detailing service.',
                'value' => 800.00,
                'weight' => 20, // 20% chance
                'stock' => 75,
                'metadata' => ['service_type' => 'detailing', 'duration' => '1 session']
            ],
            [
                'code' => 'RANDOM_WEEKEND_TRIP',
                'type' => 'random_gift',
                'name' => 'Weekend Getaway Voucher',
                'description' => '$2,000 voucher for a luxury weekend getaway experience.',
                'value' => 2000.00,
                'weight' => 5, // 5% chance - rare prize
                'stock' => 25,
                'metadata' => ['validity' => '1 year', 'type' => 'travel_voucher']
            ]
        ];

        // DISCOUNT VOUCHERS - Direct price reduction
        $discountVouchers = [
            [
                'code' => 'DISC_5PCT',
                'type' => 'discount',
                'name' => 'Giảm giá trực tiếp $2,000',
                'description' => 'Voucher giảm trực tiếp $2,000 vào đơn đặt cọc.',
                'value' => 2000.00,
                'usage_limit_per_user' => 1,
                'active' => true,
            ],
        ];

        // VIP TIER VOUCHERS - Loyalty program
        $vipTierVouchers = [
            // Silver VIP
            [
                'code' => 'SILVER_PRIORITY_SERVICE',
                'type' => 'vip_tier',
                'name' => 'Priority Service Access',
                'description' => 'Skip the line with priority service booking and dedicated support.',
                'value' => null,
                'tier_level' => 'silver',
                'metadata' => ['priority_level' => 2, 'service_type' => 'priority_access']
            ],
            [
                'code' => 'SILVER_EXTENDED_WARRANTY',
                'type' => 'vip_tier',
                'name' => 'Extended Warranty Package',
                'description' => 'Additional 6 months warranty extension on your purchase.',
                'value' => 1500.00,
                'tier_level' => 'silver',
                'metadata' => ['duration' => '6 months', 'service_type' => 'warranty']
            ],

            // Gold Elite
            [
                'code' => 'GOLD_CONCIERGE',
                'type' => 'vip_tier',
                'name' => 'Personal Car Concierge',
                'description' => 'Dedicated personal assistant for all your car-related needs.',
                'value' => null,
                'tier_level' => 'gold',
                'metadata' => ['service_type' => 'concierge', 'duration' => '1 year']
            ],
            [
                'code' => 'GOLD_INSURANCE_DISCOUNT',
                'type' => 'vip_tier',
                'name' => 'Insurance Premium Discount',
                'description' => '$3,000 discount on comprehensive insurance premium.',
                'value' => 3000.00,
                'tier_level' => 'gold',
                'metadata' => ['service_type' => 'insurance', 'discount_type' => 'premium']
            ],

            // Platinum Exclusive
            [
                'code' => 'PLATINUM_VIP_EXPERIENCE',
                'type' => 'vip_tier',
                'name' => 'Exclusive VIP Experience',
                'description' => 'VIP access to auto shows, exclusive events, and luxury experiences.',
                'value' => 5000.00,
                'tier_level' => 'platinum',
                'metadata' => ['service_type' => 'vip_experience', 'duration' => '1 year']
            ],
            [
                'code' => 'PLATINUM_PERSONAL_SHOPPER',
                'type' => 'vip_tier',
                'name' => 'Personal Car Shopping Service',
                'description' => 'Dedicated personal shopper to help you find and customize your perfect car.',
                'value' => null
            ]
        ];

// Create all vouchers
$allVouchers = array_merge($tieredChoiceVouchers, $randomGiftVouchers, $discountVouchers);

// Normalize: ensure all vouchers are active by default unless explicitly disabled
$allVouchers = array_map(function($v) {
    if (!array_key_exists('active', $v)) {
        $v['active'] = true;
    }
    // Default per-user usage to 1 if not set for gift-like vouchers
    if (!array_key_exists('usage_limit_per_user', $v)) {
        $v['usage_limit_per_user'] = 1;
    }
    return $v;
}, $allVouchers);

$created = 0;
$updated = 0;

foreach ($allVouchers as $voucherData) {
    $existing = Voucher::where('code', $voucherData['code'])->first();
    
    if ($existing) {
        // Update existing voucher
        $existing->update($voucherData);
        $updated++;
    } else {
        // Create new voucher
        Voucher::create($voucherData);
        $created++;
    }
}

$this->command->info("Created {$created} new vouchers and updated {$updated} existing vouchers successfully!");

// VIP Tier Vouchers
$vipVouchers = [
    ['level' => 'bronze', 'name' => 'Giảm giá 5% cho phụ kiện', 'description' => 'Voucher giảm giá 5% cho lần mua phụ kiện tiếp theo.'],
    ['level' => 'silver', 'name' => 'Bảo dưỡng miễn phí 1 lần', 'description' => 'Tặng 1 lần bảo dưỡng miễn phí cho xe của bạn.'],
    ['level' => 'gold', 'name' => 'Voucher bảo hiểm vật chất $200', 'description' => 'Tặng voucher bảo hiểm vật chất trị giá $200.'],
    ['level' => 'platinum', 'name' => 'Quà tặng đặc biệt', 'description' => 'Một quà tặng đặc biệt dành riêng cho thành viên Kim Cương.'],
];

foreach ($vipVouchers as $v) {
    Voucher::updateOrCreate(
        ['code' => 'VIP_' . strtoupper($v['level'])],
        [
            'name' => $v['name'],
            'type' => 'vip_tier',
            'description' => $v['description'],
            'tier_level' => $v['level'],
            'active' => true,
            'usage_limit_per_user' => 1, // Mỗi người chỉ nhận 1 lần khi đạt hạng
        ]
    );
}

$this->command->info('VIP tier vouchers seeded.');
    }
}
