<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
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
        'can_sorted',
        'waste_category_id'
    ];

    public function scopeLatestPriceByCustomerCategory(Builder $query, int $customer_category_id, $transactionDate)
    {
        return $query->with('wastePrices', function ($query) use ($transactionDate, $customer_category_id) {
            $query->where('customer_category_id', $customer_category_id)
                ->where('effective_start_date', '<=', $transactionDate)
                ->latest('effective_start_date')
                ->first();
        });
    }

    public function latestPrice()
    {
        return $this->hasOne(WastePrice::class)->latestOfMany();
    }

    public function category(): BelongsTo
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

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
