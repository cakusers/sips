<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteTransaction extends Model
{
    protected $fillable = [
        'waste_id',
        'transaction_id',
        'qty_in_gram',
        'transaction_waste_price'
    ];
}
