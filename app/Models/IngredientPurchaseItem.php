<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientPurchaseItem extends Model
{
    protected $fillable = [
        'ingredient_purchase_id',
        'ingredient_id',
        'ingredient_name',
        'qty',
        'unit',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(IngredientPurchase::class, 'ingredient_purchase_id');
    }
}
