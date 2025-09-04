<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;
use App\Filament\Widgets\Sales\RevenueChart;
use App\Filament\Widgets\Sales\PurchaseChart;
use App\Filament\Widgets\Sales\FinanceOverview;
use App\Filament\Widgets\Sales\GrossProfitChart;

class SalesDashboard extends \Filament\Pages\Dashboard
{
    protected static string $routePath = 'sales';
    protected static ?string $title = 'Dashboard Penjualan';
    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?string $navigationGroup = 'Dashboard';
    protected static ?int $navigationSort = 1;
    public static function canAccess(): bool
    {
        return Auth::user()->role === UserRole::OWNER || Auth::user()->role === UserRole::ADMIN;
    }

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
