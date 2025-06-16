<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Illuminate\Support\Facades\DB;

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
        if ($transaction->isDirty('status')) {
            if ($transaction->wasChanged('status')) {

                // Skenario 1: Transaksi menjadi SELESAI (dari status apapun sebelumnya)
                // Ini adalah saat stok utama harus dikurangi/ditambah.
                if ($transaction->status === TransactionStatus::COMPLETE) {
                    $this->adjustStockForCompletion($transaction);
                }

                // Skenario 2: Transaksi yang SUDAH SELESAI dikembalikan
                // Kita cek status aslinya adalah COMPLETE
                if (
                    $transaction->status === TransactionStatus::RETURNED &&
                    $transaction->getOriginal('status') === TransactionStatus::COMPLETE
                ) {
                    $this->reverseStockForReturn($transaction);
                }
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
     * Menyesuaikan stok saat sebuah transaksi ditandai Selesai (COMPLETE).
     * Dipanggil dari event updated().
     */
    /**
     * Menyesuaikan stok saat transaksi ditandai SELESAI.
     */
    private function adjustStockForCompletion(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            foreach ($transaction->transactionWastes()->get() as $item) {
                $waste = $item->waste;
                if ($transaction->type === TransactionType::SELL) {
                    // Saat JUAL, stok berkurang
                    $waste->stock_in_kg -= $item->qty_in_kg;
                } elseif ($transaction->type === TransactionType::PURCHASE) {
                    // Saat BELI, stok bertambah
                    $waste->stock_in_kg += $item->qty_in_kg;
                }
                $waste->save();
            }
        });
    }

    private function reverseStockForReturn(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            foreach ($transaction->transactionWastes()->get() as $item) {
                $waste = $item->waste;
                if ($transaction->type === TransactionType::SELL) {
                    // Pengembalian dari JUAL, stok bertambah kembali
                    $waste->stock_in_kg += $item->qty_in_kg;
                } elseif ($transaction->type === TransactionType::PURCHASE) {
                    // Pengembalian dari BELI, stok berkurang kembali
                    $waste->stock_in_kg -= $item->qty_in_kg;
                }
                $waste->save();
            }
        });
    }
}
