<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Filament\Actions;
use App\Models\Customer;
use App\Models\Transaction;
use App\Enums\PaymentStatus;
use App\Enums\TransactionStatus;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\TransactionResource;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    public $isFormDisabled = false;

    public function mount($record): void
    {
        parent::mount($record);
        $transaction = $this->getRecord();
        if ($transaction->status === TransactionStatus::COMPLETE || $transaction->status === TransactionStatus::CANCELED || $transaction->status === TransactionStatus::RETURNED) {
            $this->isFormDisabled = true;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('status_display')
                ->label(fn($record) => 'Status Transaksi Sekarang : ' . match ($record->status) {
                    TransactionStatus::NEW => 'Baru',
                    TransactionStatus::COMPLETE => 'Selesai',
                    TransactionStatus::DELIVERED => 'Dikirim',
                    TransactionStatus::CANCELED => 'Dibatalkan',
                    TransactionStatus::RETURNED => 'Dikembalikan',
                })
                ->color(fn($record): string => match ($record->status) {
                    TransactionStatus::NEW => 'info',
                    TransactionStatus::COMPLETE => 'success',
                    TransactionStatus::DELIVERED => 'darkBlue',
                    TransactionStatus::CANCELED => 'danger',
                    TransactionStatus::RETURNED => 'purple',
                })
                ->outlined()
                ->extraAttributes([
                    'style' => 'opacity:100%;',
                ])
                ->disabled(),

            Actions\Action::make('print-invoice')
                ->button()
                ->label('Print Nota')
                ->icon('heroicon-s-printer')
                ->visible(fn(Transaction $record): bool => $record->status === TransactionStatus::COMPLETE && $record->payment_status === PaymentStatus::PAID)
                ->url(fn(Transaction $record) => route('print-invoice', [$record->id])),

            Actions\Action::make('complete')
                ->button()
                ->label('Selesaikan Transaksi')
                ->icon('heroicon-s-check-circle')
                ->color('success')
                ->visible(function (Transaction $record) {
                    $isValid = $record->status === TransactionStatus::NEW || $record->status === TransactionStatus::DELIVERED;
                    $isPaid = $this->data['payment_status'] === PaymentStatus::PAID->value;

                    return $isValid && $isPaid;
                })
                ->action(function (Transaction $record) {
                    // Stok Otomatis terubah dengan Observer.
                    $formData = $this->form->getState();
                    $formData['status'] = TransactionStatus::COMPLETE;
                    $record->update($formData);
                    $this->isFormDisabled = true;

                    Notification::make()->title('Transaksi ditandai Selesai')->success()->send();
                    // return redirect(static::getUrl(['record' => $record]));
                }),

            // Actions\Action::make('deliver')
            //     ->button()
            //     ->label('Kirim')
            //     ->color('info')
            //     ->icon('heroicon-s-truck')
            //     ->visible(fn(Transaction $transaction) => $transaction->status === TransactionStatus::NEW)
            //     ->action(function (Transaction $record) {
            //         $record->status = TransactionStatus::DELIVERED;
            //         $record->save();
            //         Notification::make()->title('Transaksi ditandai Dikirimkan')->success()->send();
            //     }),

            Actions\Action::make('cancelTransaction')
                ->button()
                ->label('Batalkan Transaksi')
                ->color('danger')
                ->icon('heroicon-s-x-circle')
                ->requiresConfirmation()
                ->visible(fn(Transaction $transaction) => $transaction->status === TransactionStatus::NEW || $transaction->status === TransactionStatus::DELIVERED)
                ->action(function (Transaction $record) {
                    $formData = $this->form->getState();
                    $formData['status'] = TransactionStatus::CANCELED;
                    $record->update($formData);
                    $this->isFormDisabled = true;

                    Notification::make()->title('Transaksi Dibatalkan')->success()->send();
                    // return redirect(static::getUrl(['record' => $record]));
                }),

            Actions\Action::make('return')
                ->button()
                ->outlined()
                ->label('Pengembalian Transaksi')
                ->color('purple')
                ->icon('heroicon-s-arrow-uturn-left')
                ->requiresConfirmation()
                ->visible(fn(Transaction $record): bool => $record->status === TransactionStatus::COMPLETE)
                ->action(function (Transaction $record) {
                    // Mengubah Stok dengan Observer.
                    $record->status = TransactionStatus::RETURNED;
                    $record->save();
                    $this->isFormDisabled = true;

                    Notification::make()->title('Pengembalian transaksi berhasil diproses')->success()->send();
                }),
        ];
    }
}
