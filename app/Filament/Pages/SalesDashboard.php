<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FinanceOverview;
use App\Filament\Widgets\GrossProfitChart;
use App\Filament\Widgets\PurchaseChart;
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
            GrossProfitChart::class,
            RevenueChart::class,
            PurchaseChart::class,

            // TransactionOverview::class,
        ];
    }
}
