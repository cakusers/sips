<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\MovementType;
use App\Models\StockMovement;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StockMovementResource\Pages;
use App\Filament\Resources\StockMovementResource\Pages\AdjustStockMovement;
use Illuminate\Support\HtmlString;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Umum';
    protected static ?string $navigationLabel = 'Data Stok';
    protected static ?string $label = 'Data Stok';
    protected static ?int $navigationSort = 5;

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\Select::make('waste_id')
                        ->relationship('waste', 'name')
                        ->label('Sampah'),
                    Forms\Components\Select::make('transaction_id')
                        ->relationship('transaction', 'id')
                        ->label('ID Transaksi'),
                ])
                    ->columns(2),
                Forms\Components\Section::make()->schema([
                    Forms\Components\Select::make('type')
                        ->label('Tipe Perubahan')
                        ->options([
                            MovementType::PURCHASEIN->value => 'Pembelian Masuk',
                            MovementType::SELLOUT->value => 'Penjualan Keluar',
                            MovementType::RETURNEDIN->value => 'Pengembalian Masuk',
                            MovementType::RETURNEDOUT->value => 'Pengembalian Keluar',
                            MovementType::MANUALIN->value => 'Penyesuaian Manual Masuk',
                            MovementType::MANUALOUT->value => 'Penyesuaian Manual Keluar',
                            MovementType::SORTINGIN->value => 'Sortiran Masuk',
                            MovementType::SORTINGOUT->value => 'Sortiran Keluar',
                            MovementType::SORTINGADJUST->value => 'Penyesuaian Sortir'
                        ]),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->label('Dilakukan Oleh'),
                    Forms\Components\TextInput::make('quantity_change_kg')
                        ->label('Jumlah Perubahan')
                        ->formatStateUsing(fn($state) => self::strFormat($state))
                        ->suffix('Kg'),
                    Forms\Components\TextInput::make('current_stock_after_movement_kg')
                        ->label('Stok Setelah Perubahan')
                        ->formatStateUsing(fn($state) => self::strFormat($state))
                        ->suffix('Kg'),
                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi')
                        ->columnSpanFull(),
                ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal & Waktu')
                    ->dateTime('j M o, H.i')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('transaction.number')
                    ->label('Nomer Transaksi')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('waste.name')
                    ->label('Nama Sampah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('before_movement_kg')
                    ->label('Stok Awal (Kg)')
                    ->toggleable()
                    ->alignCenter()
                    ->abbr('Stok awal sebelum berubah.', asTooltip: true)
                    ->formatStateUsing(fn($state) => self::strFormat($state)),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe Perubahan')
                    ->abbr('Jenis kegiatan yang menyebabkan perubahan pada jumlah stok.', asTooltip: true)
                    ->toggleable()
                    ->badge()
                    ->color(fn(MovementType $state) => match ($state) {
                        MovementType::PURCHASEIN, MovementType::RETURNEDIN, MovementType::MANUALIN => 'info',
                        MovementType::SELLOUT, MovementType::RETURNEDOUT, MovementType::MANUALOUT, MovementType::SORTINGOUT => 'amber',
                        MovementType::SORTINGIN, MovementType::SORTINGADJUST => 'purple',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(MovementType $state): ?string => match ($state) {
                        MovementType::PURCHASEIN => 'Pembelian Masuk',
                        MovementType::SELLOUT => 'Penjualan Keluar',
                        MovementType::RETURNEDIN => 'Pengembalian Masuk',
                        MovementType::RETURNEDOUT => 'Pengembalian Keluar',
                        MovementType::MANUALIN => 'Penyesuaian Manual Masuk',
                        MovementType::MANUALOUT => 'Penyesuaian Manual Keluar',
                        MovementType::SORTINGIN => 'Sortiran Masuk',
                        MovementType::SORTINGOUT => 'Sortiran Keluar',
                        MovementType::SORTINGADJUST => 'Penyesuaian Sortir',
                    })
                    ->tooltip(fn(MovementType $state): ?string => match ($state) {
                        MovementType::PURCHASEIN => 'Penambahan stok dari pembelian sampah.',
                        MovementType::SELLOUT => 'Pengurangan stok dari penjualan sampah.',
                        MovementType::RETURNEDIN => 'Penambahan stok dari penjualan sampah yang terlanjur diselesaikan.',
                        MovementType::RETURNEDOUT => 'Pengurangan stok dari pembelian sampah yang terlanjur diselesaikan.',
                        MovementType::MANUALIN => 'Penambahan stok secara manual.',
                        MovementType::MANUALOUT => 'Pengurangan stok secara manual.',
                        MovementType::SORTINGIN => 'Penambahan stok dari hasil pemilahan.',
                        MovementType::SORTINGOUT => 'Pengurangan Stok bahan baku yang digunakan untuk pemilahan.',
                        MovementType::SORTINGADJUST => 'Koreksi jumlah stok pemilahan, bisa menambah atau mengurangi stok',
                    }),
                Tables\Columns\TextColumn::make('quantity_change_kg')
                    ->label('Stok Berubah (Kg)')
                    ->abbr('Menunjukkan perubahan jumlah stok: Hijau (+) berarti bertambah dan Merah (-) berarti berkurang.', asTooltip: true)
                    ->toggleable()
                    ->alignCenter()
                    ->formatStateUsing(
                        fn(string $state): string =>
                        (float) $state > 0.0 ? '+' . self::strFormat($state) : $state
                    )
                    ->color(
                        fn(string $state): string =>
                        (float) $state > 0.0 ? 'success' : 'danger'
                    ),
                Tables\Columns\TextColumn::make('current_stock_after_movement_kg')
                    ->label('Stok Akhir (Kg)')
                    ->abbr('Stok setelah berubah, menunjukkan stok saat ini', asTooltip: true)
                    ->toggleable()
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => self::strFormat($state)),
                Tables\Columns\TextColumn::make('carbon_footprint_change_kg_co2e')
                    ->label(new HtmlString('Dekarbonisasi (Kg CO<sub>2</sub>e)'))
                    ->formatStateUsing(fn($state) => str_replace('.', ',', abs((float) $state)))
                    ->alignCenter()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dilakukan Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Filter Tipe Pergerakan')
                    ->multiple()
                    ->options([
                        MovementType::PURCHASEIN->value => 'Pembelian Masuk',
                        MovementType::SELLOUT->value => 'Penjualan Keluar',
                        MovementType::RETURNEDIN->value => 'Pengembalian Masuk',
                        MovementType::RETURNEDOUT->value => 'Pengembalian Keluar',
                        MovementType::MANUALIN->value => 'Penyesuaian Manual Masuk',
                        MovementType::MANUALOUT->value => 'Penyesuaian Manual Keluar',
                        MovementType::SORTINGIN->value => 'Sortiran Masuk',
                        MovementType::SORTINGOUT->value => 'Sortiran Keluar',
                        MovementType::SORTINGADJUST->value => 'Penyesuaian Sortir',
                    ]),
                Tables\Filters\SelectFilter::make('waste_id')
                    ->relationship('waste', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter Sampah'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->headerActions([
                // Tables\Actions\Action::make('manual_stock_adjustment')
                //     ->label('Sesuaikan Stok')
                //     ->icon('heroicon-o-pencil-square')
                //     ->color('primary')
                //     ->url(AdjustStockMovement::getUrl()),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([]),
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
            'index' => Pages\ListStockMovements::route('/'),
            'create' => Pages\CreateStockMovement::route('/create'),
            'edit' => Pages\EditStockMovement::route('/{record}/edit'),
            'adjustment' => Pages\AdjustStockMovement::route('/stock-adjustment')
        ];
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
