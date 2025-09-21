<?php

namespace App\Filament\Resources;

use Closure;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Waste;
use App\Enums\UserRole;
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
use App\Models\CustomerCategory;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Indicator;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\Pages\EditTransaction;
use Filament\Forms\Components\Hidden;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Data Transaksi';
    protected static ?string $label = 'Data Transaksi';
    protected static ?string $navigationGroup = 'Umum';
    protected static ?int $navigationSort = 3;
    public static function canViewAny(): bool
    {
        return Auth::user()->role === UserRole::OWNER || Auth::user()->role === UserRole::ADMIN;
    }

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
                Grid::make()
                    ->columns([
                        'lg' => 2
                    ])
                    ->schema([
                        Section::make('Informasi Transaksi')
                            ->columns([
                                'lg' => 2
                            ])
                            ->columnSpan([
                                'lg' => 1
                            ])
                            ->schema([
                                Forms\Components\Radio::make('type')
                                    ->label('Tipe Transaksi')
                                    ->disabled(function ($livewire) {
                                        return is_a($livewire, EditTransaction::class) && $livewire->isFormDisabled;
                                    })
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::updatePriceAndSubTotal($get, $set, false))
                                    ->options(self::$transactionType),
                                Forms\Components\Radio::make('payment_status')
                                    ->label('Status Pembayaran')
                                    ->required()
                                    ->live()
                                    ->default(PaymentStatus::UNPAID->value)
                                    ->disabled(function ($livewire) {
                                        return is_a($livewire, EditTransaction::class) && $livewire->isFormDisabled;
                                    })
                                    ->options([
                                        PaymentStatus::PAID->value => 'Lunas',
                                        PaymentStatus::UNPAID->value => 'Belum Lunas'
                                    ]),
                                // Forms\Components\Select::make('type')
                                //     ->label('Tipe Transaksi')
                                //     ->options(self::$transactionType)
                                //     ->disabled(fn($operation) => $operation === 'edit')
                                //     ->required()
                                //     ->live()
                                //     ->afterStateUpdated(fn(Get $get, Set $set) => self::updatePriceAndSubTotal($get, $set, false)),
                                // Select::make('payment_status')
                                //     ->label('Status Pembayaran')
                                //     ->required()
                                //     ->live()
                                //     ->default(PaymentStatus::UNPAID->value)
                                //     ->disabled(fn(Get $get) => $get('status') === 'Selesai')
                                //     ->options([
                                //         PaymentStatus::PAID->value => 'Lunas',
                                //         PaymentStatus::UNPAID->value => 'Belum Lunas'
                                //     ]),
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
                            ]),

                        Section::make('Informasi Pelanggan')
                            ->columns([
                                'lg' => 2
                            ])
                            ->columnSpan([
                                'lg' => 1
                            ])
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Pelanggan')
                                    ->relationship('customer', 'name')
                                    ->placeholder('Pilih pelanggan')
                                    ->searchable()
                                    ->preload()
                                    ->live(onBlur: true)
                                    ->disabled(function ($livewire) {
                                        return is_a($livewire, EditTransaction::class) && $livewire->isFormDisabled;
                                    })
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        $customer = Customer::find($state);
                                        // Set kategori Pelanggan
                                        if ($state) {
                                            $set('customer_category_id', $customer->customer_category_id);
                                        } else {
                                            $set('customer_category_id', '');
                                        }

                                        // Set Harga sampah (jika menekan tombol ini di urutan akhir)
                                        self::updatePriceAndSubTotal($get, $set, false);
                                    })
                                    ->createOptionForm([Section::make()
                                        ->columns([
                                            'lg' => 2
                                        ])
                                        ->schema([
                                            Forms\Components\TextInput::make('name')
                                                ->label('Nama')
                                                ->required()
                                                ->maxLength(255),
                                            Forms\Components\Select::make('customer_category_id')
                                                ->label('Tipe Pelanggan')
                                                ->relationship('customerCategory', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->createOptionForm([
                                                    Forms\Components\Section::make()
                                                        ->columnSpan([
                                                            'lg' => 1
                                                        ])
                                                        ->schema([
                                                            Forms\Components\TextInput::make('name')
                                                                ->label('Nama')
                                                                ->required()
                                                                ->maxLength(255),
                                                        ]),
                                                ]),
                                            Forms\Components\TextInput::make('phone')
                                                ->label('No. Telepon')
                                                ->placeholder('081xxxxxxxxxx')
                                                ->tel()
                                                ->maxLength(20),
                                            Forms\Components\TextInput::make('email')
                                                ->email()
                                                ->maxLength(255),

                                            Forms\Components\TextInput::make('address')
                                                ->label('Alamat')
                                                ->columnSpanFull(),
                                            Textarea::make('decription')
                                                ->label('Catatan')
                                                ->columnSpanFull(),
                                        ])]),
                                Select::make('customer_category_id')
                                    ->label('Kategori Pelanggan')
                                    ->options(CustomerCategory::all()->pluck('name', 'id'))
                                    ->live(onBlur: true)
                                    ->disabled(function ($livewire) {
                                        return is_a($livewire, EditTransaction::class) && $livewire->isFormDisabled;
                                    })
                                    ->afterStateUpdated(fn(Get $get, Set $set) => self::updatePriceAndSubTotal($get, $set, false))
                                    ->dehydrated(true)
                                    ->createOptionForm([
                                        Forms\Components\Section::make()
                                            ->columnSpan([
                                                'lg' => 1
                                            ])
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nama')
                                                    ->required()
                                                    ->maxLength(255),
                                            ]),
                                    ]),
                            ]),
                    ]),


                Section::make('Detail Sampah')->schema([
                    Repeater::make('transactionWastes')
                        ->label(false)
                        ->relationship()
                        ->addActionLabel('Tambahkan Sampah yang dipilih')
                        ->minItems(1)
                        ->columns(5)
                        ->disabled(function ($livewire, Get $get) {
                            return is_a($livewire, EditTransaction::class) && $livewire->isFormDisabled;
                        })
                        ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                            $stock = Waste::find($data['waste_id'])->stock_in_kg;
                            $stock = self::strFormat($stock);
                            $qty = self::strFormat($data['qty_in_kg']);

                            $data['stock_in_kg'] = $stock;
                            $data['qty_in_kg'] = $qty;
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
                                        // Assign Stok tersedia
                                        $waste = Waste::find($state);
                                        if ($waste) {
                                            $stock = self::strFormat($waste->stock_in_kg);
                                            $set('stock_in_kg', $stock);
                                        } else {
                                            $set('stock_in_kg', 0);
                                        }

                                        self::updatePriceAndSubTotal($get, $set);
                                    }
                                )
                                ->createOptionForm([
                                    Grid::make()
                                        ->columns([
                                            'sm' => 1,
                                            'lg' => 2
                                        ])
                                        ->schema([
                                            Section::make()
                                                ->columns([
                                                    'lg' => 2
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

                                            // Section::make()
                                            //     ->columnSpan([
                                            //         'lg' => 1
                                            //     ])
                                            //     ->hidden(fn($operation) => $operation === 'create')
                                            //     ->schema([
                                            //         TextInput::make('stock_in_kg')
                                            //             ->label('Stok Tersedia')
                                            //             ->disabled()
                                            //             // ->readOnly()
                                            //             // ->dehydrated(false)
                                            //             ->default(0)
                                            //             ->suffix('Kg')
                                            //             ->hidden(fn($operation) => $operation === 'create')
                                            //             ->formatStateUsing(fn($state) => str_replace('.', ',', $state))
                                            //     ]),

                                            Section::make()
                                                ->columnSpanFull()
                                                ->schema([
                                                    FileUpload::make('img')
                                                        ->label('Gambar Sampah')
                                                        ->image()
                                                        ->directory('sampah')
                                                        ->visibility('private'),
                                                ])
                                        ])
                                ]),

                            TextInput::make('qty_in_kg')
                                ->label('Berat')
                                ->default(0)
                                ->required()
                                ->suffix('Kg')
                                ->live(onBlur: true)
                                ->dehydrateStateUsing(fn(?string $state) => self::floatFormat($state))
                                ->afterStateUpdated(fn(Get $get, Set $set) => self::updatePriceAndSubTotal($get, $set))
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
                                ->dehydrated(false)
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

                Section::make()
                    ->schema([
                        TextInput::make('total_price')
                            ->label('Harga Total')
                            ->prefix('Rp')->numeric()
                            ->required()
                            ->readOnly()
                            ->disabled(function ($livewire, Get $get) {
                                return is_a($livewire, EditTransaction::class) && $livewire->isFormDisabled;
                            }),
                    ]),
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
                    ->default('-')
                    ->searchable()
                    ->sortable()
                    ->limit(15)
                    ->toggleable(),
                TextColumn::make('customerCategory.name')
                    ->label('Kategori Pelanggan')
                    ->limit(15)
                    ->default('-')
                    ->searchable()
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
                    ->dateTime('j M o, H.i')
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
                SelectFilter::make('customer_id')
                    ->label('Pelanggan')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('customer_category_id')
                    ->label('Kategori Pelanggan')
                    ->relationship('customerCategory', 'name')
                    ->searchable()
                    ->preload(),
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

                Tables\Actions\Action::make('print-invoice')
                    ->button()
                    ->label('Print Nota')
                    ->icon('heroicon-s-printer')
                    ->visible(fn(Transaction $record): bool => $record->status === TransactionStatus::COMPLETE && $record->payment_status === PaymentStatus::PAID)
                    ->url(fn(Transaction $record) => route('print-invoice', [$record->id])),

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

    protected static function updatePriceAndSubTotal(Get $get, Set $set, bool $insideRepeater = true)
    {
        if (!$insideRepeater) {
            // Get semua field yang berhubungan dengan harga dan sub total
            $customerCategoryId =  $get('customer_category_id');
            $transactionType = $get('type');
            $transactionWastes = $get('transactionWastes');

            // Karena Transaction Waste berupa array, maka harus merubah di dalam array itu
            $updatedTransactionWastes = array_map(function ($transactionWaste) use ($transactionType, $customerCategoryId) {
                if (!$transactionType || !$customerCategoryId || !$transactionWaste['waste_id']) {
                    $transactionWaste['unit_price'] = 0;
                    $transactionWaste['sub_total_price'] = 0;
                    return $transactionWaste;
                }

                $price = WastePrice::where('waste_id', $transactionWaste['waste_id'])
                    ->where('customer_category_id', $customerCategoryId)
                    ->where('effective_start_date', '<=', now())
                    ->first();

                if (!$price) {
                    $transactionWaste['unit_price'] = 0;
                    $transactionWaste['sub_total_price'] = 0;
                    return $transactionWaste;
                }

                $unitPrice = $transactionType === TransactionType::SELL->value ? $price->selling_per_kg : $price->purchase_per_kg;
                $transactionWaste['unit_price'] = $unitPrice;

                $subTotal = self::floatFormat($transactionWaste['qty_in_kg']) * $unitPrice;
                $transactionWaste['sub_total_price'] = $subTotal;
                return $transactionWaste;
            }, $transactionWastes);

            $set('transactionWastes', $updatedTransactionWastes);
            return;
        }

        // Get semua field yang berhubungan dengan harga dan sub total
        $customerCategoryId =  $get('../../customer_category_id');
        $transactionType = $get('../../type');
        $transactionWastes = $get('../../transactionWastes');
        $wasteId = $get('waste_id');

        if (!$transactionType || !$customerCategoryId || !$wasteId) {
            $set('unit_price', 0);
            $set('sub_total_price', 0);
            return;
        }

        $price = WastePrice::where('waste_id', $wasteId)
            ->where('customer_category_id', $customerCategoryId)
            ->where('effective_start_date', '<=', now())
            ->first();

        if (!$price) {
            $set('unit_price', 0);
            $set('sub_total_price', 0);
            return;
        }

        $unitPrice = $transactionType === TransactionType::SELL->value ? $price->selling_per_kg : $price->purchase_per_kg;
        $set('unit_price', $unitPrice);

        $subTotal = self::floatFormat($get('qty_in_kg')) * $unitPrice;
        $set('sub_total_price', $subTotal);
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
