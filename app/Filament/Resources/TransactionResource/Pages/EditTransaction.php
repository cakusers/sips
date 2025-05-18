<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Filament\Actions;
use App\Models\Customer;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\TransactionResource;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {

        $data['typeHidden'] = $data['type'];
        $data['address'] = Customer::find($data['customer_id'])->address;


        return $data;
    }

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
            Actions\DeleteAction::make()->before(function (Model $record) {
                $record->load('transactionWastes.waste');

                foreach ($record->transactionWastes as $detail) {

                    $waste = $detail->waste;

                    if ($record->type === TransactionType::SELL->value) {
                        $waste->stock_in_kg += $detail->qty_in_kg;
                    } else {
                        $waste->stock_in_kg -= $detail->qty_in_kg;
                    }

                    $waste->save();
                }
            }),
        ];
    }
}
