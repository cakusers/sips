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

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Pengelolaan Sampah';
    protected static ?string $navigationLabel = 'Data Stok';
    protected static ?string $label = 'Data Stok';
    protected static ?int $navigationSort = 3;

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
                    ->dateTime('j F o, H.i')
                    ->sortable(),
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
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => self::strFormat($state)),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe Perubahan')
                    ->badge()
                    ->color(fn(MovementType $state) => match ($state) {
                        MovementType::PURCHASEIN, MovementType::RETURNEDIN, MovementType::MANUALIN => 'info',
                        MovementType::SELLOUT, MovementType::RETURNEDOUT, MovementType::MANUALOUT => 'amber',
                        MovementType::SORTINGIN => 'purple',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(MovementType $state) => match ($state) {
                        MovementType::PURCHASEIN => 'Pembelian Masuk',
                        MovementType::SELLOUT => 'Penjualan Keluar',
                        MovementType::RETURNEDIN => 'Pengembalian Masuk',
                        MovementType::RETURNEDOUT => 'Pengembalian Keluar',
                        MovementType::MANUALIN => 'Penyesuaian Manual Masuk',
                        MovementType::MANUALOUT => 'Penyesuaian Manual Keluar',
                        MovementType::SORTINGIN => 'Perubahan Setelah Dipilah',
                    }),
                Tables\Columns\TextColumn::make('quantity_change_kg')
                    ->label('Perubahan (Kg)')
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
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => self::strFormat($state)),
                // Tables\Columns\TextColumn::make('description')
                //     ->label('Deskripsi')
                //     ->words(3)
                //     ->tooltip(fn(string $state): string => $state),,
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dilakukan Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        MovementType::PURCHASEIN->value => 'Pembelian Masuk',
                        MovementType::SELLOUT->value => 'Penjualan Keluar',
                        MovementType::RETURNEDIN->value => 'Pengembalian Masuk',
                        MovementType::RETURNEDOUT->value => 'Pengembalian Keluar',
                        MovementType::MANUALIN->value => 'Penyesuaian Manual Masuk',
                        MovementType::MANUALOUT->value => 'Penyesuaian Manual Keluar',
                        MovementType::SORTINGIN->value  => 'Perubahan Setelah Dipilah',
                    ])
                    ->label('Filter Tipe Pergerakan'),
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
