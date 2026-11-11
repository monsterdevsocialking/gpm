<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteRequestItem extends Model
{
    protected $fillable = [
        'quote_request_id', 'product_id', 'product_name', 'product_sku',
        'unit', 'quantity', 'notes',
    ];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3'];
    }

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
