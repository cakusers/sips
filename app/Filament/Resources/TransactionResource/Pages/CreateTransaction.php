<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Filament\Actions;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\TransactionResource;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = TransactionStatus::NEW;
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
