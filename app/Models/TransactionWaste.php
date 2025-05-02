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

    public function waste(): BelongsTo
    {
        return $this->belongsTo(Waste::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
