<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Waste extends Model
{
    protected $fillable = [
        'name',
        'img',
        'stock_in_kg',
        'min_stock_in_kg',
        'waste_category_id'
    ];

    public function latestPrice()
    {
        return $this->hasOne(WastePrice::class)->latestOfMany();
    }

    public function wasteCategory(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class, 'waste_category_id');
    }

    public function wastePrices(): HasMany
    {
        return $this->hasMany(WastePrice::class);
    }

    // public function transactions(): BelongsToMany
    // {
    //     return $this->belongsToMany(Transaction::class);
    // }

    public function transactionWastes(): HasMany
    {
        return $this->hasMany(TransactionWaste::class);
    }
}
