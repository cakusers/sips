<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WastePrice extends Model
{
    protected $fillable = [
        'purchase_per_kg',
        'selling_per_kg',
        'waste_id',
        'customer_category_id'
    ];

    public function waste(): BelongsTo
    {
        return $this->belongsTo(Waste::class);
    }

    public function customerCategory(): BelongsTo
    {
        return $this->belongsTo(CustomerCategory::class);
    }
}
