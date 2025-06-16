<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Filament\Actions;
use App\Models\Customer;
use App\Models\Transaction;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\TransactionResource;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['address'] = Customer::find($data['customer_id'])->address;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('complete')
                ->button()
                ->label('Selesai')
                ->icon('heroicon-s-check-circle')
                ->color('success')
                ->visible(fn(Transaction $record): bool => $record->status === TransactionStatus::NEW || $record->status === TransactionStatus::DELIVERED)
                ->action(function (Transaction $record) {
                    // Mengubah Stok dengan Observer.
                    $record->status = TransactionStatus::COMPLETE;
                    $record->save();
                    Notification::make()->title('Transaksi ditandai Selesai')->success()->send();
                }),

            Actions\Action::make('deliver')
                ->button()
                ->label('Dikirim')
                ->color('info')
                ->icon('heroicon-s-truck')
                ->visible(fn(Transaction $transaction) => $transaction->status === TransactionStatus::NEW)
                ->action(function (Transaction $record) {
                    $record->status = TransactionStatus::DELIVERED;
                    $record->save();
                    Notification::make()->title('Transaksi ditandai Dikirimkan')->success()->send();
                }),

            Actions\Action::make('cancel')
                ->button()
                ->label('Batalkan')
                ->color('danger')
                ->icon('heroicon-s-x-circle')
                ->visible(fn(Transaction $transaction) => $transaction->status === TransactionStatus::NEW || $transaction->status === TransactionStatus::DELIVERED)
                ->action(function (Transaction $record) {
                    $record->status = TransactionStatus::CANCELED;
                    $record->save();
                    Notification::make()->title('Transaksi Dibatalkan')->success()->send();
                }),

            Actions\Action::make('return')
                ->button()
                ->label('Pengembalian')
                ->color('amber')
                ->icon('heroicon-s-arrow-uturn-left')
                ->requiresConfirmation()
                ->visible(fn(Transaction $record): bool => $record->status === TransactionStatus::COMPLETE)
                ->action(function (Transaction $record) {
                    // Mengubah Stok dengan Observer.
                    $record->status = TransactionStatus::RETURNED;
                    $record->save();
                    Notification::make()->title('Pengembalian transaksi berhasil diproses')->success()->send();
                }),
        ];
    }
}
