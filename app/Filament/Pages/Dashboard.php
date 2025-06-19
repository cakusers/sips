<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FinanceOverview;
use App\Filament\Widgets\GrossProfitChart;
use App\Filament\Widgets\ProfitOverview;
use App\Filament\Widgets\PurchaseChart;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\RevenueOverview;
use App\Filament\Widgets\TransactionChart;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends \Filament\Pages\Dashboard
{

    // use HasFiltersForm;

    protected static ?string $title = 'Dashboard';

    // public function getColumns(): int|string|array
    // {
    //     return [
    //         'sm' => 2,
    //         'md' => 4,
    //         'xl' => 6
    //     ];
    // }

    // public function filtersForm(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Section::make()
    //                 ->schema([
    //                     DatePicker::make('startDate'),
    //                     DatePicker::make('endDate'),
    //                 ])
    //                 ->columns(3),
    //         ]);
    // }

    public function getWidgets(): array
    {
        return [
            FinanceOverview::class,
            RevenueChart::class,
            PurchaseChart::class,
            GrossProfitChart::class
        ];
    }
}
