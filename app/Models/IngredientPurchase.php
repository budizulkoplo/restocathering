<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IngredientPurchase extends Model
{
    protected $fillable = [
        'invoice_number',
        'purchase_date',
        'supplier_name',
        'notes',
        'total',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(IngredientPurchaseItem::class);
    }
}
