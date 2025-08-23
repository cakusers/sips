<?php

namespace App\Filament\Resources;

use Closure;
use Dom\Text;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Waste;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Customer;
use Filament\Forms\Form;
use App\Models\WastePrice;
use Filament\Tables\Table;
use App\Models\Transaction;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Indicator;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransactionResource\Pages;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Data Transaksi';
    protected static ?string $label = 'Data Transaksi';
    protected static ?string $navigationGroup = 'Umum';
    protected static ?int $navigationSort = 3;

    protected static array $transactionType = [
        TransactionType::PURCHASE->value => 'Beli',
        TransactionType::SELL->value => 'Jual'
    ];

    protected static array $paymentStatus = [
        PaymentStatus::PAID->value => 'Lunas',
        PaymentStatus::UNPAID->value => 'Belum Lunas'
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
                Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipe Transaksi')
                            ->options(self::$transactionType)
                            ->disabled(fn($operation) => $operation === 'edit')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {

                                // Ambil semua item yang ada di repeater
                                $items = $get('transactionWastes');

                                if (!is_array($items) || empty($items)) {
                                    return;
                                }

                                $updatedItems = [];
                                $totalPrice = 0;
                                $transactionDate = now();

                                foreach ($items as $key => $itemData) {
                                    $wasteId = $itemData['waste_id'];
                                    $qty = self::floatFormat($itemData['qty_in_kg']);

                                    // Jika item tersebut sudah memiliki pilihan sampah
                                    if ($wasteId) {
                                        // Ambil harga master yang relevan
                                        $priceRecord = WastePrice::query()
                                            ->where('waste_id', $wasteId)
                                            ->where('effective_start_date', '<=', $transactionDate)
                                            ->latest('effective_start_date')->first();

                                        $newUnitPrice = 0;
                                        if ($priceRecord) {
                                            // Tentukan harga satuan baru berdasarkan TIPE TRANSAKSI yang baru
                                            $newUnitPrice = ($state === TransactionType::SELL->value)
                                                ? $priceRecord->selling_per_kg
                                                : $priceRecord->purchase_per_kg;
                                        }

                                        // Perbarui data untuk item ini
                                        $itemData['unit_price'] = $newUnitPrice;
                                        $itemData['sub_total_price'] = $qty * $newUnitPrice;
                                    }

                                    $updatedItems[$key] = $itemData;
                                    $totalPrice += (float) ($itemData['sub_total_price']);
                                }

                                $set('transactionWastes', $updatedItems);
                                $set('total_price', $totalPrice);
                            }),

                        Select::make('customer_id')
                            ->label('Pelanggan')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(onBlur: true)
                            ->disabled(fn(Get $get) => $get('status') !== 'Baru')
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

                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->required()
                            ->live()
                            ->default(PaymentStatus::UNPAID->value)
                            ->disabled(fn(Get $get) => $get('status') === 'Selesai')
                            ->options([
                                PaymentStatus::PAID->value => 'Lunas',
                                PaymentStatus::UNPAID->value => 'Belum Lunas'
                            ]),
                        Forms\Components\TextInput::make('status')
                            ->default(TransactionStatus::NEW->value)
                            ->dehydrated(false)
                            ->live()
                            ->hidden()
                            ->formatStateUsing(
                                fn($state): string => match ($state) {
                                    TransactionStatus::NEW->value => 'Baru',
                                    TransactionStatus::COMPLETE->value => 'Selesai',
                                    TransactionStatus::DELIVERED->value => 'Dikirimkan',
                                    TransactionStatus::CANCELED->value => 'Dibatalkan',
                                    TransactionStatus::RETURNED->value => 'Dikembalikan',
                                }
                            )
                    ])->columns(3),

                Section::make('Detail Sampah')->schema([
                    Repeater::make('transactionWastes')
                        ->label(false)
                        ->relationship()
                        ->addActionLabel('Tambahkan Sampah yang dipilih')
                        ->minItems(1)
                        ->columns(5)
                        ->disabled(fn(Get $get) => $get('status') !== 'Baru')
                        ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                            $stock = Waste::find($data['waste_id'])->stock_in_kg;
                            $stock = self::strFormat($stock);
                            $qty = self::strFormat($data['qty_in_kg']);

                            $data['stock_in_kg'] = $stock;
                            $data['qty_in_kg'] = $qty;
                            return $data;
                        })
                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {

                            $data['qty_in_kg'] = self::floatFormat($data['qty_in_kg']);
                            return $data;
                        })
                        ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                            $data['qty_in_kg'] = self::floatFormat($data['qty_in_kg']);
                            return $data;
                        })
                        ->schema([
                            Select::make('waste_id')
                                ->relationship('waste', 'name')
                                ->label('Sampah')
                                ->preload()
                                ->searchable()
                                ->required()
                                ->live(onBlur: true)
                                ->distinct()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->afterStateUpdated(
                                    function (Get $get, Set $set, ?string $state) {

                                        if (blank($state)) {
                                            $set('unit_price', 0);
                                            return;
                                        }

                                        $transactionDate = $get('../../created_at') ?? now();

                                        $waste = Waste::where('id', $state)
                                            ->with(['wastePrices' => function ($query) use ($transactionDate) {
                                                $query->where('effective_start_date', '<=', $transactionDate)
                                                    ->latest('effective_start_date');
                                            }])
                                            ->first();

                                        $price = $waste->wastePrices->first();
                                        $stock = self::strFormat($waste->stock_in_kg);
                                        $set('stock_in_kg', $stock);

                                        if ($price) {
                                            $transactionType = $get('../../type');
                                            $unitPrice = ($transactionType === TransactionType::SELL->value)
                                                ? $price->selling_per_kg
                                                : $price->purchase_per_kg;
                                            $set('unit_price', $unitPrice);
                                        }

                                        $qty = self::floatFormat($get('qty_in_kg'));
                                        $unitPrice = (int) $get('unit_price');
                                        $set('sub_total_price', $qty * $unitPrice);
                                    }
                                )
                                ->createOptionForm([
                                    Grid::make()
                                        ->columns(2)
                                        ->schema([
                                            Section::make()->schema([
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
                                            ])->columnSpan(1),

                                            Section::make()->schema([
                                                TextInput::make('purchase_per_kg')
                                                    ->prefix('Rp')
                                                    ->label('Harga Beli per Kg')
                                                    ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state))
                                                    ->required(),

                                                TextInput::make('selling_per_kg')
                                                    ->prefix('Rp')
                                                    ->label('Harga Jual per Kg')
                                                    ->dehydrateStateUsing(fn($state) => str_replace('.', '', $state))
                                                    ->required(),
                                            ])->columnSpan(1),

                                            Section::make()->schema([
                                                TextInput::make('stock_in_kg')
                                                    ->label('Stok Tersedia')
                                                    ->readOnly()
                                                    ->default(0)
                                                    ->suffix('Kg')
                                                    ->dehydrateStateUsing(fn($state) => str_replace(',', '.', $state)),

                                            ])->columnSpan(1),
                                            Section::make()->schema([
                                                FileUpload::make('img')
                                                    ->label('Gambar Sampah')
                                                    ->image()
                                                    ->directory('sampah')
                                                    ->visibility('private'),
                                            ])->columnSpan(1)
                                        ])
                                ])
                                ->createOptionUsing(function (array $data): int {
                                    $waste = Waste::create([
                                        'name' => $data['name'],
                                        'img' => $data['img'],
                                        'waste_category_id' => $data['waste_category_id'],
                                        // 'min_stock_in_kg' => $data['min_stock_in_kg']
                                    ]);

                                    WastePrice::create([
                                        'waste_id' => $waste->id,
                                        'purchase_per_kg' => $data['purchase_per_kg'],
                                        'selling_per_kg' => $data['selling_per_kg'],
                                    ]);

                                    return $waste->id;
                                }),

                            TextInput::make('qty_in_kg')
                                ->label('Berat')
                                ->default(0)
                                ->required()
                                ->minValue(0.1)
                                ->suffix('Kg')
                                ->live(onBlur: true)
                                ->afterStateUpdated(
                                    function (Get $get, Set $set, $state) {
                                        $qty = self::floatFormat($state);
                                        $unitPrice = (int) $get('unit_price');
                                        $set('sub_total_price', $qty * $unitPrice);
                                    }
                                )
                                ->rules([
                                    // Cek Berat lebih dari 0
                                    fn() =>
                                    function (string $attribute, $value, Closure $fail) {
                                        $qty = self::floatFormat($value);
                                        if ($qty <= 0.0) {
                                            Notification::make()->title('Data tidak berhasil disimpan')->danger()->send();
                                            $fail("Berat harus lebih dari 0");
                                        }
                                    },
                                    // Cek ketika menjual barang, stoknya harus tersedia
                                    fn(Get $get): Closure =>
                                    function (string $attribute, $value, Closure $fail) use ($get) {
                                        $qty = self::floatFormat($value);
                                        if ($get('../../type') !== TransactionType::SELL->value) {
                                            return;
                                        }

                                        $wasteId = $get('waste_id');
                                        if (!$wasteId) {
                                            $waste = Waste::find($wasteId);
                                            $currentStock = $waste->stock_in_kg;
                                        } else {
                                            $currentStock = $get('stock_in_kg');
                                        };

                                        if ($qty > $currentStock) {
                                            Notification::make()->title('Data tidak berhasil disimpan')->danger()->send();
                                            $fail("Stok tidak cukup. Stok yang tersedia " . str_replace('.', ',', $currentStock) . " Kg.");
                                        }
                                    },
                                ]),

                            TextInput::make('stock_in_kg')
                                ->label('Stok Tersedia')
                                ->suffix('Kg')
                                ->default(0)
                                ->readOnly(),

                            TextInput::make('unit_price')
                                ->label(fn(Get $get): string => ($get('../../type') === TransactionType::SELL->value) ? 'Harga Jual/Kg' : 'Harga Beli/Kg')
                                ->numeric()->required()->prefix('Rp')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    $qty = self::floatFormat($get('qty_in_kg'));
                                    $unitPrice = (int) $state;
                                    $set('sub_total_price', $qty * $unitPrice);
                                }),

                            TextInput::make('sub_total_price')
                                ->label('Sub Total')
                                ->numeric()->required()->prefix('Rp')
                                ->readOnly(),
                        ])
                        ->addAction(
                            fn(Get $get, Set $set) => self::updateTotalPrice($get, $set)
                        )
                        ->deleteAction(
                            fn(Action $action) => $action->after(fn(Get $get, Set $set) => self::updateTotalPrice($get, $set))
                        )

                ]),

                Section::make()->schema([
                    TextInput::make('total_price')
                        ->label('Harga Total')
                        ->prefix('Rp')->numeric()
                        ->required()
                        ->readOnly()
                        ->disabled(fn(Get $get) => $get('status') !== 'Baru'),
                    TextInput::make('address')
                        ->label('Alamat Pelanggan')
                        ->disabled(fn(Get $get) => $get('status') !== 'Baru')
                        ->readOnly(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Nomer Transaksi')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable()
                    ->limit(15)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(fn($state) => $state === TransactionType::SELL ? 'Jual' : 'Beli')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Harga Total')
                    ->numeric()
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->alignment(Alignment::Center)
                    ->toggleable()
                    ->color(
                        fn($state): string => match ($state) {
                            TransactionStatus::NEW => 'info',
                            TransactionStatus::COMPLETE => 'success',
                            TransactionStatus::DELIVERED => 'darkBlue',
                            TransactionStatus::CANCELED => 'danger',
                            TransactionStatus::RETURNED => 'purple',
                        }
                    )
                    ->formatStateUsing(
                        fn($state): string => match ($state) {
                            TransactionStatus::NEW => 'Baru',
                            TransactionStatus::COMPLETE => 'Selesai',
                            TransactionStatus::DELIVERED => 'Dikirimkan',
                            TransactionStatus::CANCELED => 'Dibatalkan',
                            TransactionStatus::RETURNED => 'Dikembalikan',
                        }
                    ),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label("Status Pembayaran")
                    ->badge()
                    ->alignment(Alignment::Center)
                    ->toggleable()
                    ->color(
                        fn($state): string => match ($state) {
                            PaymentStatus::PAID => 'success',
                            PaymentStatus::UNPAID => 'danger',
                        }
                    )
                    ->formatStateUsing(
                        fn($state): string => match ($state) {
                            PaymentStatus::PAID => 'Lunas',
                            PaymentStatus::UNPAID => 'Belum Lunas',
                        }
                    ),
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
                    ->label('Tipe Transaksi')
                    ->options(self::$transactionType),
                SelectFilter::make('status')
                    ->multiple()
                    ->label('Status Transaksi')
                    ->options(self::$transactionStatus),
                SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options(self::$paymentStatus),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Transaksi Pada Tanggal')
                            ->placeholder('dd/mm/yy')
                            ->displayFormat('d F Y')
                            ->maxDate(fn(Get $get) => $get('created_until') ?? now())
                            ->live(),
                        DatePicker::make('created_until')
                            ->label('Transaksi Hingga Tanggal')
                            ->placeholder('dd/mm/yy')
                            ->displayFormat('d F Y')
                            ->minDate(fn(Get $get) => $get('created_from'))
                            ->maxDate(now())
                            ->live(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators[] = Indicator::make('Dari ' . Carbon::parse($data['created_from'])->toFormattedDateString('d F Y'))
                                ->removeField('created_from');
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators[] = Indicator::make('Sampai ' . Carbon::parse($data['created_until'])->toFormattedDateString('d F Y'))
                                ->removeField('created_until');
                        }
                        return $indicators;
                    })

            ])
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->button()
                    ->label('Selesaikan')
                    ->icon('heroicon-s-check-circle')
                    ->color('success')
                    ->visible(fn(Transaction $record): bool => ($record->status === TransactionStatus::NEW || $record->status === TransactionStatus::DELIVERED) && $record->payment_status === PaymentStatus::PAID)
                    ->action(function (Transaction $record) {
                        // Mengubah Stok dengan Observer.
                        $record->status = TransactionStatus::COMPLETE;
                        $record->save();
                        Notification::make()->title('Transaksi ditandai Selesai')->success()->send();
                    }),

                // Tables\Actions\Action::make('deliver')
                //     ->button()
                //     ->label('Dikirim')
                //     ->color('info')
                //     ->icon('heroicon-s-truck')
                //     ->visible(fn(Transaction $transaction) => $transaction->status === TransactionStatus::NEW)
                //     ->action(function (Transaction $record) {
                //         $record->status = TransactionStatus::DELIVERED;
                //         $record->save();
                //         Notification::make()->title('Transaksi ditandai Dikirimkan')->success()->send();
                //     }),

                Tables\Actions\Action::make('cancel')
                    ->button()
                    ->label('Batalkan')
                    ->color('danger')
                    ->icon('heroicon-s-x-circle')
                    ->visible(fn(Transaction $transaction) => $transaction->status === TransactionStatus::NEW || $transaction->status === TransactionStatus::DELIVERED)
                    ->action(function (Transaction $record) {
                        $record->status = TransactionStatus::CANCELED;
                        $record->save();
                        Notification::make()->title('Transaksi Dibatalkan')->success()->send();
                    }),

                Tables\Actions\Action::make('return')
                    ->button()
                    ->outlined()
                    ->label('Pengembalian')
                    ->color('purple')
                    ->icon('heroicon-s-arrow-uturn-left')
                    ->requiresConfirmation()
                    ->visible(fn(Transaction $record): bool => $record->status === TransactionStatus::COMPLETE)
                    ->action(function (Transaction $record) {
                        // Mengubah Stok dengan Observer.
                        $record->status = TransactionStatus::RETURNED;
                        $record->save();
                        Notification::make()->title('Pengembalian transaksi berhasil diproses')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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

    /**
     * Fungsi Helper untuk Update Total Price
     */
    private static function updateTotalPrice(Get $get, Set $set): void
    {
        $total = 0;
        $items = $get('transactionWastes');

        if (is_array($items)) {
            foreach ($items as $item) {
                $subTotalValue = (int) $item['sub_total_price'] ?? '0';
                $total += $subTotalValue;
            }
        }
        $set('total_price', $total);
    }

    private static function strFormat(float $number): string
    {
        return str_replace('.', ',', $number ?? 0);
    }

    private static function floatFormat(string $number): float
    {
        return (float) str_replace(',', '.', $number ?? '0');
    }
}
