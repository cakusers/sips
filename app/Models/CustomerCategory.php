<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerCategory extends Model
{
    protected $fillable = [
        'name'
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
