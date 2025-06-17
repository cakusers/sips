<?php

namespace App\Observers;

use App\Enums\MovementType;
use App\Models\Transaction;
use App\Models\StockMovement;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        // Pastikan perubahan terjadi pada kolom 'status'
        if ($transaction->isDirty('status')) {

            $oldStatus = $transaction->getOriginal('status');
            $newStatus = $transaction->status;

            // Logika utama: Stok dihitung HANYA saat status menjadi COMPLETE atau RETURNED.

            // Skenario 1: Transaksi menjadi COMPLETE
            // Ini bisa dari status NEW atau DELIVERED
            if ($newStatus === TransactionStatus::COMPLETE && $oldStatus !== TransactionStatus::COMPLETE) {
                $this->adjustStockAndLogMovement($transaction, 'complete');
            }

            // Skenario 2: Transaksi menjadi RETURNED
            // Ini hanya bisa dari status COMPLETE (karena ada tombol 'returned' setelah complete)
            if ($newStatus === TransactionStatus::RETURNED && $oldStatus === TransactionStatus::COMPLETE) {
                $this->adjustStockAndLogMovement($transaction, 'returned');
            }
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Menyesuaikan stok dan mencatat pergerakan stok berdasarkan tipe perubahan transaksi.
     */
    private function adjustStockAndLogMovement(Transaction $transaction, string $triggerType): void
    {
        DB::transaction(function () use ($transaction, $triggerType) {
            foreach ($transaction->transactionWastes()->get() as $item) {
                $waste = $item->waste;
                $quantity = $item->qty_in_kg;
                $movementType = '';
                $descText = '';

                if ($triggerType === 'complete') {
                    if ($transaction->type === TransactionType::SELL) {
                        // Transaksi JUAL menjadi COMPLETE: Stok BERKURANG
                        $waste->stock_in_kg -= $quantity;
                        $movementType = MovementType::SELLOUT;
                        $descText = 'Penjualan selesai (Transaksi ID: ' . $transaction->id . ')';
                    } elseif ($transaction->type === TransactionType::PURCHASE) {
                        // Transaksi BELI menjadi COMPLETE: Stok BERTAMBAH
                        $waste->stock_in_kg += $quantity;
                        $movementType = MovementType::PURCHASEIN;
                        $descText = 'Pembelian selesai (Transaksi ID: ' . $transaction->id . ')';
                    }
                } elseif ($triggerType === 'returned') {
                    // Hanya terjadi jika sebelumnya COMPLETE
                    if ($transaction->type === TransactionType::SELL) {
                        // Transaksi JUAL di-RETURNED: Stok BERTAMBAH kembali
                        // Ini membalik efek dari penjualan yang sudah selesai
                        $waste->stock_in_kg += $quantity;
                        $movementType = MovementType::RETURNEDIN;
                        $descText = 'Pengembalian dari penjualan (Transaksi ID: ' . $transaction->id . ')';
                    } elseif ($transaction->type === TransactionType::PURCHASE) {
                        // Transaksi BELI di-RETURNED: Stok BERKURANG kembali
                        // Ini membalik efek dari pembelian yang sudah selesai
                        $waste->stock_in_kg -= $quantity;
                        $movementType = MovementType::RETURNEDOUT;
                        $descText = 'Pengembalian dari pembelian (Transaksi ID: ' . $transaction->id . ')';
                    }
                }

                $waste->save();
                StockMovement::create([
                    'waste_id' => $waste->id,
                    'type' => $movementType,
                    'quantity_change_kg' => $quantity,
                    'current_stock_after_movement_kg' => $waste->stock_in_kg,
                    'description' => $descText,
                    'transaction_id' => $transaction->id,
                    'user_id' => Auth::id(),
                ]);
            }
        });
    }
}
