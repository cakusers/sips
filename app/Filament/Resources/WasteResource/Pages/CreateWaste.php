<?php

namespace App\Filament\Resources\WasteResource\Pages;

use App\Filament\Resources\WasteResource;
use App\Models\Waste;
use App\Models\WastePrice;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateWaste extends CreateRecord
{
    protected static string $resource = WasteResource::class;

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
