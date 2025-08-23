<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\MixedWaste;
use Filament\Tables\Table;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use App\Models\TransactionWaste;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
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
    protected static ?string $navigationGroup = 'Umum';
    public static ?int $navigationSort = 4;

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
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn(Model $record): string => self::getUrl('sort', ['record' => $record]))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Dilakukan pada')
                    ->dateTime('j F o, H.i')
                    ->sortable(),
                TextColumn::make('transaction.number')
                    ->label('Nomer Transaksi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('transaction.customer.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->limit(15),
                TextColumn::make('is_sorted')
                    ->label('Status Pemilahan')
                    ->alignCenter()
                    ->badge()
                    ->color(fn($state) => $state ? 'info' : 'amber')
                    ->formatStateUsing(fn($state) => $state ? 'Sudah Dipilah' : 'Belum Dipilah'),
                TextColumn::make('qty_in_kg')
                    ->label('Berat (Kg)')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('transaction.status')
                    ->label('Status Transaksi')
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
                    )
            ])
            ->filters([
                TernaryFilter::make('is_sorted')
                    ->label('Status Pemilahan')
                    ->nullable()
                    ->placeholder('Semua')
                    ->trueLabel('Sudah Dipilah')
                    ->falseLabel('Belum Dipilah'),
                SelectFilter::make('customer')
                    ->label('Pelanggan')
                    ->relationship('transaction.customer', 'name')
                    ->searchable()
                    ->preload(),
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
