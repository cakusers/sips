<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Enums\TransactionType;
use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;



    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['type'] === TransactionType::SELL->value) {
            $data['total_price'] = (int) str_replace('.', '', $data['total_price_sell']);
        } else {
            $data['total_price'] = (int) str_replace('.', '', $data['total_price_purchase']);
        }

        return $data;
    }



    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
