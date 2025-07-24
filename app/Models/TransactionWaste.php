<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionWaste extends Pivot
{
    public $incrementing = true;

    protected $fillable = [
        'waste_id',
        'transaction_id',
        'qty_in_kg',
        'sub_total_price',
        'unit_price',
        'is_sorted',
        'sorted_from_id'
    ];

    // protected static function booted(): void
    // {
    //     static::deleting(function (TransactionWaste $detail) {
    //         $transaction = $detail->transaction()->first();
    //         $waste = $detail->waste()->first();

    //         if (!$transaction || !$waste) return;

    //         if ($transaction->type === 'purchase') {
    //             $waste->stock_in_kg -= $detail->qty_in_kg;
    //         } else {
    //             $waste->stock_in_kg += $detail->qty_in_kg;
    //         }

    //         $waste->save();
    //     });
    // }

    // Relasi untuk sampah campuran induk
    public function parentMixedWaste(): BelongsTo
    {
        return $this->belongsTo(TransactionWaste::class, 'sorted_from_id');
    }
    // Relasi untuk sampah hasil sortiran
    public function sortedWastes(): HasMany
    {
        return $this->hasMany(TransactionWaste::class, 'sorted_from_id');
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
