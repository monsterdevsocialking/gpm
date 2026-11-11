<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id', 'label', 'address', 'street', 'neighborhood',
        'reference_point', 'city', 'province', 'is_default',
    ];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
