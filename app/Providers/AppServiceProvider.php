<?php

namespace App\Providers;

use App\Models\Transaction;
use App\Models\TransactionWaste;
use App\Observers\TransactionObserver;
use Illuminate\Support\ServiceProvider;
use App\Observers\TransactionWasteObserver;
use App\Services\NumberService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Format Angka
        $this->app->singleton(NumberService::class, function ($app) {
            // Membuat instance NumberService dengan locale aplikasi.
            return new NumberService($app->getLocale());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Transaction::observe(TransactionObserver::class);
        TransactionWaste::observe(TransactionWasteObserver::class);
    }
}
