<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\MixedWaste;
use Filament\Tables\Table;
use App\Enums\TransactionType;
use App\Models\TransactionWaste;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MixedWasteResource\Pages;
use App\Filament\Resources\MixedWasteResource\RelationManagers;

class MixedWasteResource extends Resource
{

    protected static ?string $model = TransactionWaste::class;
    // protected static bool $shouldRegisterNavigation = false;
    public static ?string $label = 'Daftar Sampah Campuran';
    public static ?string $navigationLabel = 'Sortir Sampah Campuran';
    public static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationGroup = 'Pengelolaan Sampah';
    public static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('waste', function ($query) {
                $query->where('name', 'like', '%' . 'campuran' . '%');
            }))
            ->recordUrl(fn(Model $record): string => self::getUrl('sort', ['record' => $record]))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Dilakukan pada')
                    ->dateTime('j F o, H.i'),
                TextColumn::make('transaction.number')
                    ->label('Nomer Transaksi'),
                TextColumn::make('transaction.customer.name')
                    ->label('Pelanggan')
                    ->limit(15),
                TextColumn::make('is_sorted')
                    ->label('Status Pemilahan')
                    ->badge()
                    ->color(fn($state) => $state ? 'info' : 'amber')
                    ->formatStateUsing(fn($state) => $state ? 'Sudah Dipilah' : 'Belum Dipilah'),
                TextColumn::make('qty_in_kg')
                    ->label('Berat (Kg)')
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListMixedWastes::route('/'),
            'create' => Pages\CreateMixedWaste::route('/create'),
            'edit' => Pages\EditMixedWaste::route('/{record}/edit'),
            'sort' => Pages\SortMixedWaste::route('/{record}/sort')
        ];
    }
}
