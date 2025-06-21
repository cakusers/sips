<?php

namespace App\Filament\Widgets;

use App\Models\Waste;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowestStockWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Waste::query()
                    ->orderBy('stock_in_kg', 'asc')
                    ->limit(20)
            )
            ->heading('Stok Sampah Terendah')
            ->description('20 sampah dengan stok paling sedikit.')
            ->paginated()
            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5, 10, 20])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Sampah'),

                Tables\Columns\TextColumn::make('stock_in_kg')
                    ->label('Sisa Stok')
                    ->numeric()
                    ->suffix(' kg'),
            ]);
    }
}
