<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TransactionWaste extends Pivot
{
    public $incrementing = true;

    protected $fillable = [
        'waste_id',
        'transaction_id',
        'qty_in_kg',
        'sub_total_price'
    ];

    protected static function booted(): void
    {
        static::deleting(function (TransactionWaste $detail) {
            $transaction = $detail->transaction()->first();
            $waste = $detail->waste()->first();

            if (!$transaction || !$waste) return;

            if ($transaction->type === 'purchase') {
                $waste->stock_in_kg -= $detail->qty_in_kg;
            } else {
                $waste->stock_in_kg += $detail->qty_in_kg;
            }

            $waste->save();
        });
    }

    public function waste(): BelongsTo
    {
        return $this->belongsTo(Waste::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
