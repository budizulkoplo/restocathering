<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    protected $fillable = [
        'code',
        'name',
        'unit',
        'stock',
        'minimum_stock',
        'latest_purchase_price',
        'price_unit_quantity',
        'is_active',
    ];

    protected $casts = [
        'stock' => 'decimal:3',
        'minimum_stock' => 'decimal:3',
        'latest_purchase_price' => 'decimal:2',
        'price_unit_quantity' => 'decimal:3',
        'is_active' => 'bool',
    ];

    protected $appends = [
        'unit_cost',
    ];

    public function menuCompositions(): HasMany
    {
        return $this->hasMany(MenuItemIngredient::class);
    }

    public function getUnitCostAttribute(): float
    {
        $baseQuantity = max((float) $this->price_unit_quantity, 1);

        return (float) $this->latest_purchase_price / $baseQuantity;
    }
}
