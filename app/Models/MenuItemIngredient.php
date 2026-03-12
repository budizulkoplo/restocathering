<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemIngredient extends Model
{
    protected $fillable = [
        'menu_item_id',
        'ingredient_id',
        'quantity',
        'unit',
        'ingredient_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'ingredient_cost' => 'decimal:2',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
