<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;

    public function getHeaderActions(): array
    {
        return [
            Actions\Action::make('manual_stock_adjustment')
                ->label('Sesuaikan Stok')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->url(AdjustStockMovement::getUrl())
        ];
    }
}
