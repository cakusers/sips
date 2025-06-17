<?php

namespace App\Filament\Resources;

use App\Enums\MovementType;
use App\Filament\Resources\StockMovementResource\Pages;
use App\Filament\Resources\StockMovementResource\RelationManagers;
use App\Models\StockMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Pengelolaan Sampah';
    protected static ?string $navigationLabel = 'Riwayat Stok';

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
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('type')
                        ->label('Tipe Perubahan'),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->label('Dilakukan oleh'),
                    Forms\Components\TextInput::make('quantity_change_kg')
                        ->label('Jumlah Perubahan')
                        ->suffix('Kg'),
                    Forms\Components\TextInput::make('current_stock_after_movement_kg')
                        ->label('Stok Setelah Perubahan')
                        ->suffix('Kg'),
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
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('waste.name')
                    ->label('Nama Sampah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe Perubahan')
                    ->badge()
                    ->color(fn(MovementType $state) => match ($state) {
                        MovementType::PURCHASEIN, MovementType::RETURNEDIN, MovementType::MANUALIN => 'info',
                        MovementType::SELLOUT, MovementType::RETURNEDOUT, MovementType::MANUALOUT => 'amber',
                        default => 'secondary',
                    }),
                // ->formatStateUsing(fn(string $state): string => str_replace(['_', 'in', 'out'], [' ', 'masuk', 'keluar'], $state)),
                Tables\Columns\TextColumn::make('quantity_change_kg')
                    ->label('Jumlah (Kg)')
                    ->formatStateUsing(
                        fn(string $state, StockMovement $record): string =>
                        in_array($record->type, [MovementType::PURCHASEIN, MovementType::RETURNEDIN, MovementType::MANUALIN]) ? '+' . $state : '-' . $state
                    )
                    ->color(
                        fn(StockMovement $record): string =>
                        in_array($record->type, [MovementType::PURCHASEIN, MovementType::RETURNEDIN, MovementType::MANUALIN]) ? 'success' : 'danger'
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_stock_after_movement_kg')
                    ->label('Stok Akhir (Kg)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->words(10)
                    ->tooltip(fn(string $state): string => $state),
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('ID Transaksi')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
        ];
    }
}
