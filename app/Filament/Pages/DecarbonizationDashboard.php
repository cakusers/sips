<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Decarbonization\DecarbonizationInChart;
use App\Filament\Widgets\Decarbonization\DecarbonizationOutChart;
use App\Filament\Widgets\Decarbonization\DecarbonizationOverview;
use App\Filament\Widgets\Decarbonization\DecarbonizationCompositionChart;
use App\Filament\Widgets\Decarbonization\DecarbonizationInCompositionChart;
use App\Filament\Widgets\Decarbonization\DecarbonizationOutCompositionChart;

class DecarbonizationDashboard extends \Filament\Pages\Dashboard
{
    protected static string $routePath = 'decarbonization';

    protected static ?string $title = 'Dashboard Dekarbonisasi';
    protected static ?string $navigationLabel = 'Dekarbonisasi';
    protected static ?string $navigationGroup = 'Dashboard';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 2;

    public function getWidgets(): array
    {
        return [
            DecarbonizationOverview::class,
            DecarbonizationInChart::class,
            DecarbonizationOutChart::class,
            DecarbonizationCompositionChart::class,
            DecarbonizationInCompositionChart::class,
            DecarbonizationOutCompositionChart::class,
        ];
    }
}
