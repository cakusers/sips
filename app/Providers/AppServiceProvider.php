<?php

namespace App\Providers;

use App\Models\Transaction;
use App\Models\TransactionWaste;
use App\Observers\TransactionObserver;
use App\Observers\TransactionWasteObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
