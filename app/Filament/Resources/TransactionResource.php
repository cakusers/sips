<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Waste;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Support\RawJs;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $label = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    Forms\Components\Select::make('type')
                        ->label('Tipe Transaksi')
                        ->options([
                            TransactionType::PURCHASE->value => 'Pembelian',
                            TransactionType::SELL->value => 'Penjualan'
                        ])
                        ->live()
                        ->required()
                    // ->dehydrateStateUsing(function (Get $get, Set $set, $state) {
                    //     $wastes = $get('Sampah yang dipilih');
                    //     foreach ($wastes as $waste) {
                    //         if ($state === TransactionType::SELL->value) {
                    //             $set($waste['transaction_waste_price'], $waste['qty_in_gram'] * $waste['selling_price']);
                    //         } else {
                    //             $set($waste['transaction_waste_price'], $waste['qty_in_gram'] * $waste['purchase_price']);
                    //         }
                    //     }
                    //     dd($get('Sampah yang dipilih'));
                    // })
                    ,
                    Select::make('status')
                        ->options([
                            TransactionStatus::NEW->value => 'Baru',
                            TransactionStatus::COMPLETE->value => 'Selesai',
                            TransactionStatus::DELIVERED->value => 'Dikirimkan',
                            TransactionStatus::CANCELED->value => 'Dibatalkan',
                            TransactionStatus::RETURNED->value => 'Dikembalikan',
                        ])->default(TransactionStatus::NEW)
                ])->columns(2),

                Section::make()->schema([
                    Repeater::make('Sampah yang dipilih')->schema([
                        Select::make('waste_id')
                            ->label('Sampah')
                            ->relationship('wastes', 'name')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {

                                if ($state === null) {
                                    $set('selling_price', 0);
                                    $set('purchase_price', 0);
                                    $set('transaction_waste_price', $get('qty_in_gram') * 0);
                                    return;
                                }
                                $price = Waste::find($state)->latestPrice;

                                $transactionType = $get('../../type');

                                $set('selling_price', number_format($price->selling_per_kg, 0, ',', '.'));
                                $set('purchase_price', number_format($price->purchase_per_kg, 0, ',', '.'));

                                if ($transactionType === TransactionType::SELL->value) {
                                    $set('transaction_waste_price', $get('qty_in_gram') * $price->selling_per_kg);
                                } else {
                                    $set('transaction_waste_price', $get('qty_in_gram') * $price->purchase_per_kg);
                                }
                            }),

                        TextInput::make('selling_price')
                            ->label('Harga Jual/Kg')
                            ->default(0)
                            ->readOnly()
                            ->dehydrated(false)
                            ->visible(fn(Get $get) => $get('../../type') === TransactionType::SELL->value),
                        TextInput::make('purchase_price')
                            ->label('Harga Beli/Kg')
                            ->default(0)
                            ->readOnly()
                            ->dehydrated(false)
                            ->visible(fn(Get $get) => $get('../../type') === TransactionType::PURCHASE->value),

                        TextInput::make('qty_in_gram')
                            ->label('Berat (Kg)')
                            ->default(0)
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Gunakan tanda koma (,) sebagai pemisah desimal. Contoh: 5,5')
                            ->regex('/^(\d+|\d+,\d+)$/')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($livewire, $component, Get $get, Set $set, ?string $state) {

                                $livewire->validateOnly($component->getStatePath());

                                if ($get('../../type') === TransactionType::SELL->value) {
                                    $nominal = (int) str_replace('.', '', $get('selling_price'));
                                } else {
                                    $nominal = (int) str_replace('.', '', $get('purchase_price'));
                                }
                                $qtyInFloat = (float) str_replace(',', '.', $state);
                                $set('transaction_waste_price', number_format($nominal * $qtyInFloat, 0, ',', '.'));
                            }),

                        TextInput::make('transaction_waste_price')
                            ->label('Harga Total')
                            ->default(0)
                            ->readOnly()

                    ])->columns(4)
                        ->addAction(function (Get $get, Set $set) {
                            $total = $total = collect($get('Sampah yang dipilih'))->pluck('transaction_waste_price')->map(fn($item) => (int) str_replace('.', '', $item))->sum();
                            $set('price_total', number_format($total, 0, ',', '.'));
                        })
                ]),
                Section::make()->schema([
                    Forms\Components\TextInput::make('price_total')
                        ->prefix('Rp')
                        ->default(0)
                        ->readOnly(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('price_total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make()
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
