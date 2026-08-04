<?php

namespace App\Providers;

use App\Services\GoogleDriveService;
use App\Services\GoogleSheetService;
use App\Services\LocalDriveService;
use App\Services\LocalSheetService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $demo = config('google.demo_mode') || empty(config('google.sheet_id'));

        if ($demo) {
            $this->app->singleton(GoogleSheetService::class, function () {
                $service = new LocalSheetService;
                $moviesPath = storage_path('app/sheets/movies.json');
                if (! file_exists($moviesPath)) {
                    $service->seedDemoData();
                }

                return $service;
            });

            $this->app->singleton(GoogleDriveService::class, LocalDriveService::class);
        }
    }

    public function boot(): void
    {
        //
    }
}
