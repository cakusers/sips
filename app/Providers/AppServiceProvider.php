<?php

namespace App\Providers;

use App\Models\Transaction;
use App\Services\NumberService;
use App\Models\TransactionWaste;
use Illuminate\Support\HtmlString;
use App\Observers\TransactionObserver;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\ServiceProvider;
use App\Observers\TransactionWasteObserver;
use Filament\Tables\Columns\Column;

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
        if($this->app->environment('production')) {
            \URL::forceScheme('https');
        }
        Transaction::observe(TransactionObserver::class);
        TransactionWaste::observe(TransactionWasteObserver::class);
        TextColumn::macro('abbr', function (?string $abbr = null, bool $asTooltip = false): Column {
            /** @var Column $this */
            $this;

            $label = $this->getLabel();
            $abbr = $abbr ?? $label;
            $classes = $this->isSortable() ? 'cursor-pointer' : 'cursor-help';

            $attributes = $asTooltip ? 'x-tooltip.raw="' . $abbr . '" title=""' : 'title="' . $abbr . '"';

            return $this->label(new HtmlString("<abbr class=\"$classes\" $attributes>$label</abbr>"));
        });
    }
}
