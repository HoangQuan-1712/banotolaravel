<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TierService;
use App\Models\{CustomerTier, Voucher, User};

class SetupVoucherSystem extends Command
{
    protected $signature = 'voucher:setup {--fresh : Drop existing data and recreate}';
    protected $description = 'Setup the complete voucher system for car dealership';

    public function handle()
    {
        $this->info('🎁 Setting up Car Dealership Voucher System...');
        
        // Step 0: Run migrations first
        $this->info('📋 Running migrations...');
        $this->call('migrate');
        
        if ($this->option('fresh')) {
            $this->warn('⚠️  Fresh setup will delete existing voucher data!');
            if (!$this->confirm('Are you sure you want to continue?')) {
                $this->info('Setup cancelled.');
                return;
            }
            
            $this->info('🗑️  Clearing existing data...');
            
            // Check if tables exist before truncating
            try {
                Voucher::truncate();
                CustomerTier::truncate();
                
                // Reset user tier data
                User::query()->update([
                    'tier_id' => null,
                    'lifetime_spent' => 0,
                    'total_cars_bought' => 0,
                    'tier_updated_at' => null
                ]);
            } catch (\Exception $e) {
                $this->warn('Some tables may not exist yet, continuing...');
            }
        }

        // Step 1: Setup Customer Tiers
        $this->info('👑 Setting up Customer Tiers...');
        $tierService = new TierService();
        $tierService->seedTiers();
        
        $tierCount = CustomerTier::count();
        $this->info("✅ Created {$tierCount} customer tiers");

        // Step 2: Run Voucher Seeder
        $this->info('🎫 Creating Vouchers...');
        $this->call('db:seed', ['--class' => 'VoucherSeeder']);
        
        $voucherCount = Voucher::count();
        $this->info("✅ Created {$voucherCount} vouchers");

        // Step 3: Display Summary
        $this->displaySummary();
        
        $this->info('🚀 Voucher System Setup Complete!');
        $this->info('📖 Check VOUCHER_SYSTEM_SETUP.md for detailed usage instructions');
    }

    private function displaySummary()
    {
        $this->newLine();
        $this->info('📊 System Summary:');
        
        // Tier Summary
        $tiers = CustomerTier::orderBy('min_spent')->get();
        $this->table(
            ['Tier Level', 'Name', 'Min Spent', 'Discount %', 'Users'],
            $tiers->map(function($tier) {
                return [
                    strtoupper($tier->level),
                    $tier->name,
                    '$' . number_format($tier->min_spent),
                    $tier->discount_percentage . '%',
                    $tier->users()->count()
                ];
            })
        );

        // Voucher Summary by Type
        $voucherStats = Voucher::selectRaw('type, COUNT(*) as count, AVG(value) as avg_value')
            ->groupBy('type')
            ->get();
            
        $this->newLine();
        $this->info('🎁 Voucher Statistics:');
        $this->table(
            ['Type', 'Count', 'Avg Value'],
            $voucherStats->map(function($stat) {
                return [
                    ucwords(str_replace('_', ' ', $stat->type)),
                    $stat->count,
                    $stat->avg_value ? '$' . number_format($stat->avg_value, 2) : 'N/A'
                ];
            })
        );

        // Sample vouchers by type
        $this->newLine();
        $this->info('🎯 Sample Tiered Choice Vouchers:');
        $tieredChoices = Voucher::where('type', 'tiered_choice')->take(3)->get();
        foreach ($tieredChoices as $voucher) {
            $this->line("  • {$voucher->name} ({$voucher->group_code}) - \${$voucher->value}");
        }

        $this->newLine();
        $this->info('🎲 Sample Random Gift Vouchers:');
        $randomGifts = Voucher::where('type', 'random_gift')->take(3)->get();
        foreach ($randomGifts as $voucher) {
            $this->line("  • {$voucher->name} (Weight: {$voucher->weight}) - \${$voucher->value}");
        }

        $this->newLine();
        $this->info('👑 Sample VIP Tier Vouchers:');
        $vipVouchers = Voucher::where('type', 'vip_tier')->take(3)->get();
        foreach ($vipVouchers as $voucher) {
            $value = $voucher->value ? '$' . number_format($voucher->value, 2) : 'Priceless';
            $this->line("  • {$voucher->name} ({$voucher->tier_level}) - {$value}");
        }
    }
}
