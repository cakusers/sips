<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FinanceOverview;
use App\Filament\Widgets\FinancialsChart;
use App\Filament\Widgets\GrossProfitChart;
use App\Filament\Widgets\WasteMarginChart;

class SalesDashboard extends \Filament\Pages\Dashboard
{

    // use HasFiltersForm;

    protected static ?string $title = 'Dashboard Penjualan';
    protected static string $routePath = 'sales';
    protected static ?int $navigationSort = 1;

    public function getWidgets(): array
    {
        return [
            FinanceOverview::class,
            FinancialsChart::class,
            GrossProfitChart::class,
            WasteMarginChart::class,
            // StockValueCompositionChart::class,
            // LowestStockWidget::class
        ];
    }
}
