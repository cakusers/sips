<?php

namespace App\Filament\Pages;

class CarbonEmissionDashboard extends \Filament\Pages\Dashboard
{
    protected static string $routePath = 'carbon';

    protected static ?string $title = 'Dashboard Dekarbonisasi';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 2;

    public function getWidgets(): array
    {
        return [];
    }
}
