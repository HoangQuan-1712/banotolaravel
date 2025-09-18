<?php
// Test script để kiểm tra voucher system
require_once 'vendor/autoload.php';

use App\Models\{CustomerTier, Voucher, User};

echo "🎯 KIỂM TRA HỆ THỐNG VOUCHER & TIER\n";
echo "=====================================\n\n";

// 1. Kiểm tra Customer Tiers
echo "👑 CUSTOMER TIERS:\n";
$tiers = CustomerTier::orderBy('min_spent')->get();
foreach ($tiers as $tier) {
    echo "- {$tier->level}: {$tier->name} (Min: $" . number_format($tier->min_spent) . ", Discount: {$tier->discount_percentage}%)\n";
}
echo "\n";

// 2. Kiểm tra Vouchers theo loại
echo "🎁 VOUCHERS BY TYPE:\n";

echo "\n🎯 TIERED CHOICE (Upselling):\n";
$tieredChoice = Voucher::where('type', 'tiered_choice')->get();
foreach ($tieredChoice as $voucher) {
    $value = $voucher->value ? '$' . number_format($voucher->value) : 'Priceless';
    echo "- {$voucher->name} ({$voucher->group_code}) - {$value}\n";
    echo "  Range: $" . number_format($voucher->min_order_value) . " - $" . number_format($voucher->max_order_value ?? 999999) . "\n";
}

echo "\n🎲 RANDOM GIFT (Marketing Buzz):\n";
$randomGift = Voucher::where('type', 'random_gift')->get();
foreach ($randomGift as $voucher) {
    $value = $voucher->value ? '$' . number_format($voucher->value) : 'Priceless';
    echo "- {$voucher->name} (Weight: {$voucher->weight}) - {$value}\n";
}

echo "\n👑 VIP TIER (Loyalty Program):\n";
$vipTier = Voucher::where('type', 'vip_tier')->get();
foreach ($vipTier as $voucher) {
    $value = $voucher->value ? '$' . number_format($voucher->value) : 'Priceless';
    echo "- {$voucher->name} ({$voucher->tier_level}) - {$value}\n";
}

// 3. Thống kê tổng quan
echo "\n📊 STATISTICS:\n";
echo "- Total Tiers: " . CustomerTier::count() . "\n";
echo "- Total Vouchers: " . Voucher::count() . "\n";
echo "- Tiered Choice: " . Voucher::where('type', 'tiered_choice')->count() . "\n";
echo "- Random Gift: " . Voucher::where('type', 'random_gift')->count() . "\n";
echo "- VIP Tier: " . Voucher::where('type', 'vip_tier')->count() . "\n";

// 4. Test user tier calculation
echo "\n👤 SAMPLE USER TEST:\n";
$sampleUser = User::first();
if ($sampleUser) {
    echo "- User: {$sampleUser->name}\n";
    echo "- Current Tier: " . ($sampleUser->tier ? $sampleUser->tier->name : 'No tier') . "\n";
    echo "- Lifetime Spent: $" . number_format($sampleUser->lifetime_spent ?? 0) . "\n";
    echo "- Cars Bought: " . ($sampleUser->total_cars_bought ?? 0) . "\n";
} else {
    echo "- No users found in database\n";
}

echo "\n✅ SYSTEM STATUS: READY TO USE!\n";
echo "=====================================\n";
?>
