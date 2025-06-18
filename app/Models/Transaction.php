<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Transaction extends Model
{
    protected $fillable = [
        'type',
        'total_price',
        'status',
        'payment_status',
        'customer_id'
    ];

    protected $casts = [
        'status' => TransactionStatus::class,
        'type' => TransactionType::class,
        'payment_status' => PaymentStatus::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactionWastes(): HasMany
    {
        return $this->hasMany(TransactionWaste::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
