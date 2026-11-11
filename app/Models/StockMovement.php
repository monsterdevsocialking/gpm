<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class StockMovement extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'product_id', 'user_id', 'order_id', 'movement_type', 'quantity',
        'stock_before', 'stock_after', 'reason', 'reference',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'stock_before' => 'decimal:3',
            'stock_after' => 'decimal:3',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
