<?php

namespace App\Filament\Resources\MixedWasteResource\Pages;

use App\Filament\Resources\MixedWasteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMixedWaste extends EditRecord
{
    protected static string $resource = MixedWasteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
