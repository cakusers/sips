<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Transaction extends Model
{
    protected $fillable = [
        'type',
        'price_total',
        'status'
    ];

    public function wastes(): BelongsToMany
    {
        return $this->belongsToMany(Waste::class, 'waste_transaction');
    }
}
