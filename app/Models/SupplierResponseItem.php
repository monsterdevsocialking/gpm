<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierResponseItem extends Model
{
    protected $fillable = [
        'supplier_response_id', 'quote_request_item_id', 'unit_price',
        'availability_status', 'available_quantity', 'lead_time',
        'conditions', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'available_quantity' => 'decimal:3',
        ];
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(SupplierResponse::class, 'supplier_response_id');
    }

    public function quoteRequestItem(): BelongsTo
    {
        return $this->belongsTo(QuoteRequestItem::class);
    }
}
