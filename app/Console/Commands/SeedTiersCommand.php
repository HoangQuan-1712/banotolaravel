<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TierService;

class SeedTiersCommand extends Command
{
    protected $signature = 'tiers:seed';
    protected $description = 'Seed or update customer tiers based on TierService definitions';

    protected $tierService;

    public function __construct(TierService $tierService)
    {
        parent::__construct();
        $this->tierService = $tierService;
    }

    public function handle()
    {
        $this->info('Seeding customer tiers...');
        $this->tierService->seedTiers();
        $this->info('Customer tiers seeded successfully!');
        return 0;
    }
}
