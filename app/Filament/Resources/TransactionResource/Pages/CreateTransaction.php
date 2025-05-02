<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Enums\TransactionType;
use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // dd($data);
        if ($data['type'] === TransactionType::SELL->value) {
            $data['total_price'] = (int) str_replace('.', '', $data['total_price_sell']);
        } else {
            $data['total_price'] = (int) str_replace('.', '', $data['total_price_purchase']);
        }
        
        return $data;
    }



    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
