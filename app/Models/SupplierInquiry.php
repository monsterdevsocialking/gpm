<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierInquiry extends Model
{
    protected $fillable = [
        'quote_request_id', 'supplier_id', 'created_by', 'reference',
        'status', 'message', 'sent_at', 'response_due_at', 'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'response_due_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SupplierResponse::class);
    }
}
