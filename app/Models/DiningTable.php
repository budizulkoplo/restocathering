<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiningTable extends Model
{
    protected $fillable = [
        'name',
        'code',
        'capacity',
        'location',
        'qr_token',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'bool',
    ];

    public function restaurantOrders(): HasMany
    {
        return $this->hasMany(RestaurantOrder::class);
    }
}
