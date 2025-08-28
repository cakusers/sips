<?php

namespace App\Filament\Resources\WasteResource\RelationManagers;

use App\Models\CustomerCategory;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class WastePricesRelationManager extends RelationManager
{
    protected static string $relationship = 'wastePrices';

    protected $listeners = [
        'refreshHistori' => '$refresh',
    ];

    public function getTabs(): array
    {
        $customerCategories = CustomerCategory::all();
        $tabs = ['all' => Tab::make('Semua')];

        foreach ($customerCategories as $customerCategory) {
            $name = $customerCategory->name;
            $id = $customerCategory->id;

            $tabs[$id] = Tab::make($name)
                ->modifyQueryUsing(fn($query) => $query->where('customer_category_id', $id));
        }

        return $tabs;
    }

    // public function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             //,
    //         ]);
    // }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Histori Harga')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d M Y, H.m')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customerCategory.name')
                    ->label('Kategori Pelanggan')
                    ->badge(),
                // ->visible(fn() => $this->activeTab === 'all')
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
