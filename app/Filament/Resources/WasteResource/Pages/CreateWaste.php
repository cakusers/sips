<?php

namespace App\Filament\Resources\WasteResource\Pages;

use App\Models\Waste;
use Filament\Actions;
use App\Models\WastePrice;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\WasteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWaste extends CreateRecord
{
    protected static string $resource = WasteResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Action::make('back')
    //             ->label('Kembali')
    //             ->url(route('filament.admin.resources.wastes.index'))
    //             ->icon('heroicon-m-arrow-left')
    //             ->color('primary')
    //     ];
    // }

    protected function handleRecordCreation(array $data): Model
    {

        $waste = Waste::create([
            'name' => $data['name'],
            'img' => $data['img'],
            'waste_category_id' => $data['waste_category_id']
        ]);

        WastePrice::create([
            'waste_id' => $waste->id,
            'purchase_per_kg' => $this->form->getState()['purchase_per_kg'],
            'selling_per_kg' => $this->form->getState()['selling_per_kg'],
        ]);

        return $waste;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
