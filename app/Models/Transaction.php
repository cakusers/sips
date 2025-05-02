<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'type',
        'total_price',
        ''
    ];

    // public function wastes(): BelongsToMany
    // {
    //     return $this->belongsToMany(Waste::class);
    // }

    public function transactionWastes(): HasMany
    {
        return $this->hasMany(TransactionWaste::class);
    }
}
