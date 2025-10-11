<?php

namespace App\Filament\Resources\WasteResource\Pages;

use Filament\Actions;
use App\Models\WastePrice;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\WasteResource;
use App\Models\Waste;

use function PHPUnit\Framework\countOf;

class EditWaste extends EditRecord
{
    protected static string $resource = WasteResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $latestWastePrices = $this->getLatestWastePrices($this->record->id);
        // dd($latestWastePrices);
        $data['latestWastePrices'] = $latestWastePrices;
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {

        $latestWastePrices = $this->getLatestWastePrices($this->record->id);
        $latestPrices = [];
        foreach ($latestWastePrices as $price) {
            $custCategoryId = $price['customer_category_id'];
            $latestPrices[$custCategoryId] = $price;
        }

        $submittedPrices = $data['latestWastePrices'];
        // dd($submittedPrices, $latestPrices);

        foreach ($submittedPrices as $submittedPrice) {
            $custCategoryId = $submittedPrice['customer_category_id'];

            $submittedPrice['purchase_per_kg'] = (int) str_replace('.', '', $submittedPrice['purchase_per_kg']);
            $submittedPrice['selling_per_kg'] = (int) str_replace('.', '', $submittedPrice['selling_per_kg']);

            $isNewPrice = !isset($latestPrices[$custCategoryId]);
            if ($isNewPrice) {
                WastePrice::create([
                    'purchase_per_kg' => $submittedPrice['purchase_per_kg'],
                    'selling_per_kg' => $submittedPrice['selling_per_kg'],
                    'waste_id' => $this->record->id,
                    'customer_category_id' => $custCategoryId
                ]);
            } else {
                $latestPrice = $latestPrices[$custCategoryId];
                if (
                    $submittedPrice['purchase_per_kg'] != $latestPrice['purchase_per_kg']
                    || $submittedPrice['selling_per_kg'] != $latestPrice['selling_per_kg']
                ) {
                    WastePrice::create([
                        'purchase_per_kg' => $submittedPrice['purchase_per_kg'],
                        'selling_per_kg' => $submittedPrice['selling_per_kg'],
                        'waste_id' => $this->record->id,
                        'customer_category_id' => $custCategoryId
                    ]);
                }
            }
        };

        $this->dispatch('refreshHistori');

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getLatestWastePrices($wasteId)
    {
        $wastePrices = WastePrice::where('waste_id', $wasteId)
            ->latest()
            ->get()
            ->groupBy('customer_category_id');

        $latestWastePrices = [];
        foreach ($wastePrices->toArray() as $wasteprice => $value) {
            array_push($latestWastePrices, $value[0]);
        }

        return $latestWastePrices;
    }
}
