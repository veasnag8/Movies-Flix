<?php

namespace App\Console\Commands;

use App\Services\LocalSheetService;
use Illuminate\Console\Command;

class SeedDemoSheets extends Command
{
    protected $signature = 'sheets:seed-demo';

    protected $description = 'Seed local demo sheet JSON files (used when APP_DEMO_MODE=true)';

    public function handle(): int
    {
        $service = new LocalSheetService;
        $service->seedDemoData();
        $this->info('Demo sheet data seeded to storage/app/sheets');
        $this->line('Admin: admin@moviesflix.test / admin123');
        $this->line('User:  user@moviesflix.test / user123');

        return self::SUCCESS;
    }
}
