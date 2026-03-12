<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CateringOrder extends Model
{
    protected $fillable = [
        'order_number',
        'order_date',
        'event_date',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'guest_count',
        'status',
        'payment_status',
        'notes',
        'subtotal',
        'discount',
        'down_payment',
        'total',
        'cash_received',
        'change_amount',
        'balance_due',
        'stock_applied',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'event_date' => 'date',
        'guest_count' => 'integer',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'total' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'stock_applied' => 'bool',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CateringOrderItem::class);
    }
}
