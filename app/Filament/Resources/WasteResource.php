<?php

namespace App\Filament\Resources;

use Livewire\Component as Livewire;
use App\Filament\Resources\WasteResource\Pages;
use App\Filament\Resources\WasteResource\RelationManagers\WastePricesRelationManager;
use App\Models\Waste;
use App\Models\WasteCategory;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class WasteResource extends Resource
{
    protected static ?string $model = Waste::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Sampah';

    protected static ?string $navigationGroup = 'Pengelolaan Sampah';

    protected static ?string $label = 'Sampah';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->columns(2)
                    ->schema([
                        Section::make()->schema([
                            TextInput::make('name')
                                ->label('Nama')
                                ->required(),

                            Select::make('waste_category_id')
                                ->label('Kategori Sampah')
                                ->relationship('wasteCategory', 'name')
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
                        ])->columnSpan(1),

                        Section::make()->schema([
                            TextInput::make('purchase_per_kg')
                                ->prefix('Rp')
                                ->label('Harga Beli per Kg')
                                ->mask(RawJs::make(<<< 'JS'
                                    $money($input, ',')
                                JS))
                                ->stripCharacters('.')
                                ->extraAlpineAttributes([
                                    'x-ref' => 'input1',
                                    'x-on:keyup' => '$refs.input1.blur(); $refs.input1.focus()'
                                ])
                                ->required()
                                ->dehydrated(),

                            TextInput::make('selling_per_kg')
                                ->prefix('Rp')
                                ->label('Harga Jual per Kg')
                                ->mask(RawJs::make(<<< 'JS'
                                    $money($input, ',')
                                JS))
                                ->stripCharacters('.')
                                ->integer()
                                ->extraAlpineAttributes([
                                    'x-ref' => 'input2',
                                    'x-on:keyup' => '$refs.input2.blur(); $refs.input2.focus()'
                                ])
                                ->required()
                                ->dehydrated(),
                        ])->columnSpan(1),

                        // TODO: Membuat Stok editable
                        Section::make()->schema([
                            TextInput::make('stock_in_kg')
                                ->label('Stok Saat Ini')
                                ->readOnly()
                                ->default(0)
                                ->suffix('Kg')
                                ->formatStateUsing(fn($state) => str_replace('.', ',', $state))
                                ->helperText(new HtmlString('<span style="color:#ee9405">Hati-hati dalam memasukkan Stok secara manual.</span><br>Stok akan otomatis berubah bila transaksi dilakukan.')),

                            // TextInput::make('min_stock_in_kg')
                            //     ->label('Stok Minimal')
                            //     ->default(0)
                            //     ->required()
                            //     ->suffix('Kg')
                            //     ->live(onBlur: true)
                            //     ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Stok minimum pada gudang. Gunakan tanda koma (,) sebagai pemisah desimal. Contoh: 5,5')
                            //     ->regex('/^(\d+|\d+,\d+)$/')
                            //     ->validationMessages([
                            //         'regex' => 'Kolom harus berisi angka'
                            //     ])
                            //     ->formatStateUsing(fn($state) => str_replace('.', ',', $state))
                            //     ->afterStateUpdated(
                            //         function (Livewire $livewire, $component) {
                            //             $livewire->addMessagesFromOutside([
                            //                 'regex' => 'Kolom harus berisi angka'
                            //             ]);
                            //             $livewire->validateOnly($component->getStatePath());
                            //         }
                            //     )
                            //     ->dehydrateStateUsing(
                            //         fn($state) => (float) str_replace(',', '.', $state)
                            //     )

                        ])->columnSpan(1),
                        Section::make()->schema([
                            FileUpload::make('img')
                                ->label('Gambar Sampah')
                                ->image()
                                ->directory('sampah')
                                ->visibility('private'),
                        ])->columnSpan(1)
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
                    ->searchable(),
                ImageColumn::make('img')
                    ->label('Gambar')
                    ->visibility('private'),
                TextColumn::make('wasteCategory.name')
                    ->label('Kategori')
                    ->sortable(),
                TextColumn::make('latestPrice.purchase_per_kg')
                    ->label('Harga Beli')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('latestPrice.selling_per_kg')
                    ->label('Harga Jual')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('stock_in_kg')
                    ->label('Stok Tersedia')
                    ->suffix(' Kg')
                    ->color(fn(string $state) => $state === '0' ? 'danger' : ''),

            ])
            ->filters([
                SelectFilter::make('Kategori')->relationship('wasteCategory', 'name')
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
