<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (isset($data['password'])) {
            $record->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
            ]);
        }
        $record->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['email'],
            'role' => $data['role'],
        ]);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
