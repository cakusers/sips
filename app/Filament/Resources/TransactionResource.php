<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Waste;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Filament\Resources\Resource;
use Livewire\Component as Livewire;
use Illuminate\Validation\Validator;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransactionResource\Pages;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $label = 'Transaksi';

    protected static array $transactionType = [
        TransactionType::PURCHASE->value => 'Pembelian',
        TransactionType::SELL->value => 'Penjualan'
    ];

    protected static array $transactionStatus = [
        TransactionStatus::NEW->value => 'Baru',
        TransactionStatus::COMPLETE->value => 'Selesai',
        TransactionStatus::DELIVERED->value => 'Dikirimkan',
        TransactionStatus::CANCELED->value => 'Dibatalkan',
        TransactionStatus::RETURNED->value => 'Dikembalikan',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    Forms\Components\Select::make('type')
                        ->label('Tipe Transaksi')
                        ->options(self::$transactionType)
                        ->required()
                        ->live(),

                    Select::make('customer_id')
                        ->label('Pelanggan')
                        ->relationship('customer', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Set $set, $state) => $state !== null ? $set('address', (Customer::where('id', $state)->first()->address)) : $set('address', ''))
                        ->createOptionForm([
                            Section::make()->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->label('No. Telepon')
                                    ->placeholder('081xxxxxxxxxx')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('address')
                                    ->label('Alamat')
                                    ->required(),
                                Textarea::make('decription')
                                    ->label('Deskripsi')
                                    ->columnSpanFull(),
                            ])
                        ]),

                    Select::make('status')
                        ->options(self::$transactionStatus)
                        ->default(TransactionStatus::NEW)
                ])->columns(3),

                Section::make()->schema([
                    Repeater::make('transactionWastes')
                        ->relationship()
                        ->addActionLabel('Tambahkan Sampah yang dipilih')
                        ->schema([
                            Select::make('waste_id')
                                ->relationship('waste', 'name')
                                ->label('Sampah')
                                ->preload()
                                ->searchable()
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(
                                    function (Get $get, Set $set, ?string $state) {

                                        if ($state === null) {
                                            $set('selling_price', 0);
                                            $set('purchase_price', 0);
                                            $set('sub_total_sell', $get('qty_in_kg') * 0);
                                            $set('sub_total_purchase', $get('qty_in_kg') * 0);
                                            return;
                                        }

                                        $price = Waste::find($state)->latestPrice;

                                        $set('selling_price', number_format($price->selling_per_kg, 0, ',', '.'));
                                        $set('purchase_price', number_format($price->purchase_per_kg, 0, ',', '.'));

                                        $set('sub_total_sell', number_format($get('qty_in_kg') * $price->selling_per_kg,  0, ',', '.'));
                                        $set('sub_total_purchase', number_format($get('qty_in_kg') * $price->purchase_per_kg,  0, ',', '.'));
                                    }
                                ),

                            TextInput::make('qty_in_kg')
                                ->label('Berat (Kg)')
                                ->default(0)
                                ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Gunakan tanda koma (,) sebagai pemisah desimal. Contoh: 5,5')
                                ->suffix('Kg')
                                ->regex('/^(\d+|\d+,\d+)$/')
                                ->validationMessages([
                                    'regex' => 'Kolom harus berisi angka'
                                ])
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(
                                    function (Livewire $livewire, $component, Get $get, Set $set, ?string $state) {
                                        // Live validation: true
                                        $livewire
                                            ->addMessagesFromOutside([
                                                'regex' => 'Kolom harus berisi angka'
                                            ]);
                                        $livewire->validateOnly($component->getStatePath());

                                        $qtyInFloat = (float) str_replace(',', '.', $state);

                                        $sellPrice = (int) str_replace('.', '', $get('selling_price'));
                                        $set('sub_total_sell', number_format($sellPrice * $qtyInFloat, 0, ',', '.'));

                                        $purchasePrice = (int) str_replace('.', '', $get('purchase_price'));
                                        $set('sub_total_purchase', number_format($purchasePrice * $qtyInFloat, 0, ',', '.'));
                                    }
                                )
                                ->formatStateUsing(fn($state) => str_replace('.', ',', $state)),

                            TextInput::make('selling_price')
                                ->label('Harga Jual/Kg')
                                ->prefix('Rp')
                                ->default(0)
                                ->readOnly()
                                ->dehydrated(false)
                                ->live()
                                ->afterStateUpdated(fn($state) => dd($state))
                                ->visible(fn(Get $get) => $get('../../type') === TransactionType::SELL->value),
                            TextInput::make('purchase_price')
                                ->label('Harga Beli/Kg')
                                ->prefix('Rp')
                                ->default(0)
                                ->readOnly()
                                ->dehydrated(false)
                                ->visible(fn(Get $get) => $get('../../type') === TransactionType::PURCHASE->value),


                            TextInput::make('sub_total_sell')
                                ->label('Sub Total')
                                ->prefix('Rp')
                                ->default(0)
                                ->readOnly()
                                ->visible(fn(Get $get) => $get('../../type') === TransactionType::SELL->value),

                            TextInput::make('sub_total_purchase')
                                ->label('Sub Total')
                                ->prefix('Rp')
                                ->default(0)
                                ->readOnly()
                                ->visible(fn(Get $get) => $get('../../type') === TransactionType::PURCHASE->value),

                        ])
                        ->live()
                        ->columns(4)
                        ->addAction(
                            function ($state, Set $set) {
                                $collection = collect($state);
                                $totalSell = $collection->pluck('sub_total_sell')->map(fn($item) => (int) str_replace('.', '', $item))->sum();
                                $totalPurchase = $collection->pluck('sub_total_purchase')->map(fn($item) => (int) str_replace('.', '', $item))->sum();

                                $set('total_price_sell', number_format($totalSell, 0, ',', '.'));
                                $set('total_price_purchase', number_format($totalPurchase, 0, ',', '.'));
                            }
                        )
                        ->deleteAction(
                            function (Action $action) {
                                $action->after(function (Set $set, $state) {
                                    $collection = collect($state);
                                    $totalSell = $collection->pluck('sub_total_sell')->map(fn($item) => (int) str_replace('.', '', $item))->sum();
                                    $totalPurchase = $collection->pluck('sub_total_purchase')->map(fn($item) => (int) str_replace('.', '', $item))->sum();

                                    $set('total_price_sell', number_format($totalSell, 0, ',', '.'));
                                    $set('total_price_purchase', number_format($totalPurchase, 0, ',', '.'));
                                });
                            },
                        )
                        ->mutateRelationshipDataBeforeCreateUsing(
                            function (array $data, Get $get): array {

                                $data['qty_in_kg'] = (float) str_replace(',', '.', $data['qty_in_kg']);
                                if ($get('type') === TransactionType::SELL->value) {
                                    $data['sub_total_price'] = (int) str_replace('.', '', $data['sub_total_sell']);
                                } else {
                                    $data['sub_total_price'] = (int) str_replace('.', '', $data['sub_total_purchase']);
                                }

                                return $data;
                            }
                        )
                        ->mutateRelationshipDataBeforeFillUsing(
                            function (array $data, Get $get): array {

                                $price = Waste::find($data['waste_id'])->latestPrice;

                                if ($get('type') === TransactionType::SELL->value) {
                                    $data['selling_price'] = number_format($price->selling_per_kg, 0, ',', '.');
                                    $data['sub_total_sell'] = number_format($data['sub_total_price'], 0, ',', '.');
                                } else {
                                    $data['purchase_price'] = number_format($price->purchase_per_kg, 0, ',', '.');
                                    $data['sub_total_purchase'] = number_format($data['sub_total_price'], 0, ',', '.');
                                }

                                return $data;
                            }
                        )
                        ->mutateRelationshipDataBeforeSaveUsing(
                            function (array $data, Get $get): array {

                                $data['qty_in_kg'] = (float) str_replace(',', '.', $data['qty_in_kg']);
                                if ($get('type') === TransactionType::SELL->value) {
                                    $data['sub_total_price'] = (int) str_replace('.', '', $data['sub_total_sell']);
                                } else {
                                    $data['sub_total_price'] = (int) str_replace('.', '', $data['sub_total_purchase']);
                                }

                                return $data;
                            }
                        )
                ]),

                Section::make()->schema([
                    Forms\Components\TextInput::make('total_price_sell')
                        ->label('Harga Total')
                        ->prefix('Rp')
                        ->default(0)
                        ->readOnly()
                        ->visible(fn(Get $get) => $get('type') === TransactionType::SELL->value || $get('type') === null),
                    Forms\Components\TextInput::make('total_price_purchase')
                        ->label('Harga Total')
                        ->prefix('Rp')
                        ->default(0)
                        ->readOnly()
                        ->visible(fn(Get $get) => $get('type') === TransactionType::PURCHASE->value),
                ])->columnSpan(1),

                Section::make()->schema([
                    TextInput::make('address')
                        ->label('Alamat Pelanggan')
                        ->readOnly()
                ])
                    ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn($state) => $state === TransactionType::SELL ? 'Pembelian' : 'Penjualan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Harga Total')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                SelectColumn::make('status')
                    ->label('Status Transaksi')
                    ->options(self::$transactionStatus),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dilakukan Pada')
                    ->dateTime('j F o, H.i')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diupdate Pada')
                    ->dateTime('j F o, H.i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->options(self::$transactionType),
                SelectFilter::make('status')
                    ->options(self::$transactionStatus)
            ])
            ->actions([
                ViewAction::make(),
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
