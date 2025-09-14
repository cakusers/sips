<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use Filament\Tables;
use App\Models\Waste;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Livewire\Component as Livewire;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\WasteResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\WasteResource\RelationManagers\WastePricesRelationManager;
use Illuminate\Support\Facades\Auth;

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
                Section::make()
                    ->columns([
                        'lg' => 3
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
                        Radio::make('can_sorted')
                            ->label('Bisa  Disortir?')
                            ->required()
                            ->inline()
                            ->inlineLabel(false)
                            ->options([
                                true => 'Iya',
                                false => 'Tidak',
                            ]),
                    ]),
                Section::make()
                    ->hidden(fn() => Auth::user()->role === UserRole::SORTER)
                    ->schema([
                        Repeater::make('wastePrices')
                            ->label('Harga Sampah yang Berlaku')
                            ->minItems(1)
                            ->addActionLabel('Tambahkan Harga Sampah')
                            ->columns([
                                'lg' => 3
                            ])
                            ->relationship()
                            ->schema([
                                Select::make('customer_category_id')
                                    ->label('Kategori Pelanggan')
                                    ->relationship('customerCategory', 'name')
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->createOptionForm([
                                        Section::make()
                                            ->columnSpan([
                                                'lg' => 1
                                            ])
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Nama')
                                                    ->required()
                                                    ->maxLength(255),
                                            ]),
                                    ]),
                                TextInput::make('purchase_per_kg')
                                    ->label('Harga beli per kg')
                                    ->required()
                                    ->prefix('Rp')
                                    ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)),
                                TextInput::make('selling_per_kg')
                                    ->label('Harga jual per kg')
                                    ->required()
                                    ->prefix('Rp')
                                    ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state)),
                            ])
                    ]),

                Section::make()
                    ->columnSpan([
                        'lg' => 1
                    ])
                    ->hidden(fn($operation) => $operation === 'create')
                    ->schema([
                        TextInput::make('stock_in_kg')
                            ->label('Stok Tersedia')
                            ->disabled()
                            // ->readOnly()
                            // ->dehydrated(false)
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
