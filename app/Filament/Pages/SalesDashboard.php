<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FinanceOverview;
use App\Filament\Widgets\FinancialsChart;
use App\Filament\Widgets\CashFlowOverview;
use App\Filament\Widgets\GrossProfitChart;
use App\Filament\Widgets\NetCashFlowChart;
use App\Filament\Widgets\WasteMarginChart;
use App\Filament\Widgets\TransactionOverview;
use App\Filament\Widgets\CashFlowSectionHeader;
use App\Filament\Widgets\RevenueChart;

class SalesDashboard extends \Filament\Pages\Dashboard
{
    protected static string $routePath = 'sales';
    protected static ?string $title = 'Dashboard Penjualan';
    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?string $navigationGroup = 'Dashboard';
    protected static ?int $navigationSort = 1;

    public function getWidgets(): array
    {
        return [
            FinanceOverview::class,
            RevenueChart::class

            // TransactionOverview::class,
            // FinancialsChart::class,
            // GrossProfitChart::class,

            // WasteMarginChart::class,
            // StockValueCompositionChart::class,
            // LowestStockWidget::class
        ];
    }
}
