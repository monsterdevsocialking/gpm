<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class Proforma extends Model
{
    protected $fillable = [
        'order_id', 'proforma_number', 'company_name', 'company_nif',
        'company_address', 'company_phone', 'company_email', 'customer_name',
        'customer_nif', 'customer_phone', 'customer_email', 'customer_address',
        'subtotal', 'delivery_fee', 'discount', 'total', 'status',
        'issue_date', 'valid_until', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'issue_date' => 'date',
            'valid_until' => 'date',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
