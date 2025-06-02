<?php

namespace App\Filament\Resources\WasteResource\RelationManagers;


use Filament\Forms\Form;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class WastePricesRelationManager extends RelationManager
{
    protected static string $relationship = 'wastePrices';

    protected $listeners = [
        'refreshHistori' => '$refresh',
    ];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //,
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Histori Harga')
            ->recordTitleAttribute('purchase_per_kg')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchase_per_kg')
                    ->label('Harga Beli')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('selling_per_kg')
                    ->label('Harga Jual')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
