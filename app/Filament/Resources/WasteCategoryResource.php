<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WasteCategoryResource\Pages;
use App\Filament\Resources\WasteCategoryResource\RelationManagers;
use App\Models\WasteCategory;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class WasteCategoryResource extends Resource
{
    protected static ?string $model = WasteCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $label = 'Data Kategori Sampah';

    protected static ?string $navigationLabel = 'Data Kategori Sampah';

    protected static ?string $navigationGroup = 'Pengelolaan Sampah';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')
                        ->label('Nama')
                        ->required(),
                    TextInput::make('emission_factor')
                        ->label('Faktor Emisi')
                        ->formatStateUsing(fn($state) => str_replace('.', ',', $state))
                        ->dehydrateStateUsing(fn($state) => (float) str_replace(',', '.', $state))

                ])->columnSpan(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('emission_factor')
                    ->label('Faktor Emisi')
                    ->sortable()
                    ->html()
                    ->suffix(new HtmlString(' Kg CO<sub>2</sub>e/Kg'))
                    ->formatStateUsing(fn($state) => str_replace('.', ',', $state))
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
            'index' => Pages\ListWasteCategories::route('/'),
            'create' => Pages\CreateWasteCategory::route('/create'),
            'edit' => Pages\EditWasteCategory::route('/{record}/edit'),
        ];
    }
}
