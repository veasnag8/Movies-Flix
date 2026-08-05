<?php

namespace App\Providers;

use App\Services\GoogleDriveService;
use App\Services\GoogleSheetService;
use App\Services\LocalDriveService;
use App\Services\LocalSheetService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('api-public', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        RateLimiter::for('api-search', function (Request $request) {
            return Limit::perMinute(40)->by($request->ip());
        });

        RateLimiter::for('api-auth', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->ip()),
                Limit::perHour(50)->by($request->ip()),
            ];
        });

        RateLimiter::for('api-user', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api-admin', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });
    }
}
