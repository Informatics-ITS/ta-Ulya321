<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MidtransPaymentService;
use Midtrans\Config;

class MidtransServiceProvider extends ServiceProvider
{
     public function register(): void
    {
        $this->app->singleton(MidtransPaymentService::class, function ($app) {
            return new MidtransPaymentService();
        });
    }

    public function boot()
    {
    }
}