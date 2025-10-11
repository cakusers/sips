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

    protected ?bool $hasDatabaseTransactions = true;

    protected function handleRecordCreation(array $data): Model
    {
        $waste = static::getModel()::create($data);
        $wastePrices = $data['latestWastePrices'];

        foreach ($wastePrices as $wastePrice) {
            WastePrice::create([
                'purchase_per_kg' => $wastePrice['purchase_per_kg'],
                'selling_per_kg' => $wastePrice['selling_per_kg'],
                'waste_id' => $waste->id,
                'customer_category_id' => $wastePrice['customer_category_id']
            ]);
        };

        return $waste;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
