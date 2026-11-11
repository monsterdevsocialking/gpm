<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteRequest extends Model
{
    protected $fillable = [
        'request_number', 'customer_id', 'customer_name', 'customer_email',
        'customer_phone', 'status', 'notes', 'submitted_at',
    ];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteRequestItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(QuoteRequestStatusHistory::class);
    }

    public function supplierInquiries(): HasMany
    {
        return $this->hasMany(SupplierInquiry::class);
    }
}
