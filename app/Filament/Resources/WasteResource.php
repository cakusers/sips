<?php

namespace App\Filament\Resources;

use Livewire\Component as Livewire;
use App\Filament\Resources\WasteResource\Pages;
use App\Filament\Resources\WasteResource\RelationManagers\WastePricesRelationManager;
use App\Models\Waste;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WasteResource extends Resource
{
    protected static ?string $model = Waste::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Data Sampah';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $label = 'Data Sampah';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->columns([
                        'sm' => 1,
                        'lg' => 2
                    ])
                    ->schema([
                        Section::make()
                            ->columnSpan([
                                'lg' => 1
                            ])
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama')
                                    ->required(),

                                Select::make('waste_category_id')
                                    ->label('Kategori Sampah')
                                    ->relationship('category', 'name')
                                    ->preload()
                                    ->native(false)
                                    // ->optionsLimit(50)
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('Kategori')
                                            ->required()
                                    ]),
                            ]),

                        Section::make()
                            ->columnSpan([
                                'lg' => 1
                            ])
                            ->schema([
                                TextInput::make('purchase_per_kg')
                                    ->prefix('Rp')
                                    ->label('Harga Beli per Kg')
                                    ->required(),

                                TextInput::make('selling_per_kg')
                                    ->prefix('Rp')
                                    ->label('Harga Jual per Kg')
                                    ->required(),
                            ]),

                        Section::make()
                            ->columnSpan([
                                'lg' => 1
                            ])
                            ->hidden(fn($operation) => $operation === 'create')
                            ->schema([
                                TextInput::make('stock_in_kg')
                                    ->label('Stok Tersedia')
                                    ->readOnly()
                                    ->default(0)
                                    ->suffix('Kg')
                                    ->formatStateUsing(fn($state) => str_replace('.', ',', $state))
                            ]),

                        Section::make()
                            ->columnSpan(function ($operation) {
                                if ($operation === 'create') {
                                    return 2;
                                }

                                return 1;
                            })
                            ->schema([
                                FileUpload::make('img')
                                    ->label('Gambar Sampah')
                                    ->image()
                                    ->directory('sampah')
                                    ->visibility('private'),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                ImageColumn::make('img')
                    ->label('Gambar')
                    ->toggleable()
                    ->visibility('private'),
                TextColumn::make('category.name')
                    ->toggleable()
                    ->label('Kategori')
                    ->sortable(),
                TextColumn::make('latestPrice.purchase_per_kg')
                    ->label('Harga Beli')
                    ->toggleable()
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('latestPrice.selling_per_kg')
                    ->label('Harga Jual')
                    ->toggleable()
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('stock_in_kg')
                    ->label('Stok Tersedia')
                    ->toggleable()
                    ->suffix(' Kg')
                    ->formatStateUsing(fn($state) => str_replace('.', ',', $state))
                    ->color(fn(string $state) => $state === '0' ? 'danger' : ''),

            ])
            ->filters([
                SelectFilter::make('Kategori')->relationship('category', 'name')
            ])
            ->actions([
                ViewAction::make(),
                Tables\Actions\EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            WastePricesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWastes::route('/'),
            'create' => Pages\CreateWaste::route('/create'),
            'edit' => Pages\EditWaste::route('/{record}/edit'),
        ];
    }
}
