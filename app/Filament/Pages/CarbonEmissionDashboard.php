<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Decarbonization\CarbonCompositionChart;
use App\Filament\Widgets\Decarbonization\CarbonFootPrintOverview;
use App\Filament\Widgets\Decarbonization\CarbonInChart;

class CarbonEmissionDashboard extends \Filament\Pages\Dashboard
{
    protected static string $routePath = 'carbon';

    protected static ?string $title = 'Dashboard Jejak Karbon';
    protected static ?string $navigationLabel = 'Jejak Karbon';
    protected static ?string $navigationGroup = 'Dashboard';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 2;

    public function getWidgets(): array
    {
        return [
            CarbonFootPrintOverview::class,
            CarbonInChart::class,
            CarbonCompositionChart::class,
        ];
    }
}
