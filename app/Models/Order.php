<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'customer_id', 'responsible_user_id', 'customer_type',
        'customer_name', 'customer_nif', 'customer_phone', 'customer_email',
        'delivery_address', 'delivery_city', 'delivery_province',
        'delivery_street', 'delivery_neighborhood', 'delivery_reference',
        'delivery_fee', 'discount', 'subtotal', 'total', 'payment_method',
        'payment_status', 'status', 'notes', 'stock_deducted', 'ordered_at',
        'confirmed_at', 'completed_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'delivery_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'stock_deducted' => 'boolean',
            'ordered_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function proformas(): HasMany
    {
        return $this->hasMany(Proforma::class);
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }
}
