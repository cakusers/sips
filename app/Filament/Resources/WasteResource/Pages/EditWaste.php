<?php

namespace App\Filament\Resources\WasteResource\Pages;

use App\Filament\Resources\WasteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWaste extends EditRecord
{
    protected static string $resource = WasteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
