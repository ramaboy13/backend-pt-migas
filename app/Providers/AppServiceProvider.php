<?php

namespace App\Providers;

use App\Repositories\KomponenGajiRepository;
use App\Services\KomponenGajiService;
use App\Services\PayrollCalculationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(KomponenGajiService::class, function ($app) {
            return new KomponenGajiService(
                $app->make(KomponenGajiRepository::class),
                $app->make(PayrollCalculationService::class)
            );
        });
    }


    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::define('viewApiDocs', function ($user = null) {
            return true; // Mengizinkan akses docs API di production
        });
    }
}
