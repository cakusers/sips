<?php

namespace App\Filament\Resources\CustomerCategoryResource\Pages;

use App\Filament\Resources\CustomerCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerCategory extends EditRecord
{
    protected static string $resource = CustomerCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
