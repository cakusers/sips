<?php

namespace App\Models;

use App\Enums\MovementType;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'waste_id',
        'type',
        'before_movement_kg',
        'quantity_change_kg',
        'current_stock_after_movement_kg',
        'description',
        'transaction_id',
        'user_id',
    ];

    protected $casts = [
        'type' => MovementType::class,
    ];

    public function waste()
    {
        return $this->belongsTo(Waste::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
