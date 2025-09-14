<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Enums\UserRole;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CustomerCategory;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CustomerCategoryResource\Pages;
use App\Filament\Resources\CustomerCategoryResource\RelationManagers;

class CustomerCategoryResource extends Resource
{
    protected static ?string $model = CustomerCategory::class;
    protected static ?string $label = 'Data Kategori Pelanggan';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    public static function canViewAny(): bool
    {
        return Auth::user()->role === UserRole::OWNER || Auth::user()->role === UserRole::ADMIN;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
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
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListCustomerCategories::route('/'),
            'create' => Pages\CreateCustomerCategory::route('/create'),
            'edit' => Pages\EditCustomerCategory::route('/{record}/edit'),
        ];
    }
}
