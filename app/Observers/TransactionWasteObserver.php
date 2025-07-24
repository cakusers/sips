<?php

namespace App\Observers;

use App\Enums\MovementType;
use App\Models\StockMovement;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use App\Models\TransactionWaste;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionWasteObserver
{
    /**
     * Handle the TransactionWaste "created" event.
     */
    public function created(TransactionWaste $transactionWaste): void
    {
        $transaction = $transactionWaste->transaction;

        if (!isset($transaction)) {
            return;
        }

        if ($transaction->status === TransactionStatus::COMPLETE) {
            $waste = $transactionWaste->waste;
            $currentQty = $waste->stock_in_kg;
            $quantity = $transactionWaste->qty_in_kg;
            $movementType = '';
            $descText = '';

            if ($transaction->type === TransactionType::SELL) {
                // Transaksi JUAL menjadi COMPLETE: Stok BERKURANG
                $waste->stock_in_kg -= $quantity;
                $quantity = -$quantity;
                $movementType = MovementType::SELLOUT;
                $descText = 'Penjualan (Nomer Transaksi: ' . $transaction->number . ')';
            }

            if ($transaction->type === TransactionType::PURCHASE) {
                // Transaksi BELI menjadi COMPLETE: Stok BERTAMBAH
                $waste->stock_in_kg += $quantity;
                $movementType = MovementType::PURCHASEIN;
                $descText = 'Pembelian (Nomer Transaksi: ' . $transaction->number . ')';
            }

            $waste->save();
            StockMovement::create([
                'waste_id' => $waste->id,
                'type' => $movementType,
                'before_movement_kg' => $currentQty,
                'quantity_change_kg' => $quantity,
                'current_stock_after_movement_kg' => $waste->stock_in_kg,
                'description' => $descText,
                'transaction_id' => $transaction->id,
                'user_id' => Auth::id(),
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
            ]);
        }
    }

    /**
     * Handle the TransactionWaste "updated" event.
     */
    public function updated(TransactionWaste $transactionWaste): void
    {
        //
    }

    /**
     * Handle the TransactionWaste "deleted" event.
     */
    public function deleted(TransactionWaste $transactionWaste): void
    {
        //
    }

    /**
     * Handle the TransactionWaste "restored" event.
     */
    public function restored(TransactionWaste $transactionWaste): void
    {
        //
    }

    /**
     * Handle the TransactionWaste "force deleted" event.
     */
    public function forceDeleted(TransactionWaste $transactionWaste): void
    {
        //
    }
}
