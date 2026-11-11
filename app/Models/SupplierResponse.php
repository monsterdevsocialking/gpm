<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierResponse extends Model
{
    protected $fillable = [
        'supplier_inquiry_id', 'supplier_reference', 'status', 'currency',
        'lead_time', 'delivery_terms', 'payment_terms', 'valid_until',
        'notes', 'received_at',
    ];

    protected function casts(): array
    {
        return [
            'valid_until' => 'date',
            'received_at' => 'datetime',
        ];
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(SupplierInquiry::class, 'supplier_inquiry_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierResponseItem::class);
    }
}
