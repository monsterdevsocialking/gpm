<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Proforma extends Model
{
    protected $fillable = [
        'order_id', 'proforma_number', 'company_name', 'company_nif',
        'company_address', 'company_phone', 'company_email', 'customer_name',
        'customer_nif', 'customer_phone', 'customer_email', 'customer_address',
        'subtotal', 'delivery_fee', 'discount', 'total', 'currency', 'status',
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

    protected static function booted(): void
    {
        static::updating(function (self $proforma): void {
            if ($proforma->getOriginal('status') === 'issued') {
                throw new LogicException('Uma proforma emitida é imutável. Emita um novo documento para alterações.');
            }
        });

        static::deleting(function (self $proforma): void {
            if ($proforma->status === 'issued') {
                throw new LogicException('Uma proforma emitida não pode ser eliminada.');
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProformaItem::class);
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
