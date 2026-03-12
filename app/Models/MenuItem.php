<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_resto',
        'is_catering',
        'selling_price',
        'is_active',
    ];

    protected $casts = [
        'is_resto' => 'bool',
        'is_catering' => 'bool',
        'selling_price' => 'decimal:2',
        'is_active' => 'bool',
    ];

    protected $appends = [
        'category_label',
        'hpp',
    ];

    public function ingredients(): HasMany
    {
        return $this->hasMany(MenuItemIngredient::class);
    }

    public function restaurantOrderItems(): HasMany
    {
        return $this->hasMany(RestaurantOrderItem::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        if ($this->is_resto && $this->is_catering) {
            return 'Resto & Catering';
        }

        if ($this->is_resto) {
            return 'Resto';
        }

        if ($this->is_catering) {
            return 'Catering';
        }

        return 'Belum dikategorikan';
    }

    public function getHppAttribute(): float
    {
        return (float) $this->ingredients->sum(function (MenuItemIngredient $item) {
            return $item->quantity * $item->ingredient_cost;
        });
    }
}
