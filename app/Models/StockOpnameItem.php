<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    protected $fillable = [
        'stock_opname_id',
        'ingredient_id',
        'ingredient_name',
        'system_stock',
        'actual_stock',
        'difference',
        'unit',
        'notes',
    ];

    protected $casts = [
        'system_stock' => 'decimal:3',
        'actual_stock' => 'decimal:3',
        'difference' => 'decimal:3',
    ];

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }
}
