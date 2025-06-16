<?php

namespace App\Filament\Resources\WasteResource\Pages;

use Filament\Actions;
use App\Models\WastePrice;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\WasteResource;

class EditWaste extends EditRecord
{
    protected static string $resource = WasteResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // dd($data);
        $current_price = WastePrice::query()->where('waste_id', $data['id'])->get()->last();
        $data['purchase_per_kg'] = $current_price->purchase_per_kg;
        $data['selling_per_kg'] = $current_price->selling_per_kg;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['stock_in_kg'] = (float) str_replace(',', '.', $data['stock_in_kg']);
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {

        // dd([$record, $data]);
        $record->update($data);

        WastePrice::create([
            'waste_id' => $record->id,
            'purchase_per_kg' => $this->form->getState()['purchase_per_kg'],
            'selling_per_kg' => $this->form->getState()['selling_per_kg'],
            // 'min_stock_in_kg' => $data['min_stock_in_kg']
        ]);

        $this->dispatch('refreshHistori');

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            // Action::make('back')
            //     ->label('Kembali')
            //     ->url(route('filament.admin.resources.wastes.index'))
            //     ->icon('heroicon-m-arrow-left')
            //     ->color('primary')
        ];
    }
}
