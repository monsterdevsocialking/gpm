<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class ProformaItem extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'proforma_id',
        'product_id',
        'product_name',
        'product_sku',
        'unit',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        $guardIssuedProforma = function (self $item): void {
            $proforma = $item->proforma()->first();

            if ($proforma && $proforma->status !== 'draft') {
                throw new LogicException('Os artigos de uma proforma emitida são imutáveis.');
            }
        };

        static::creating($guardIssuedProforma);
        static::updating($guardIssuedProforma);
        static::deleting($guardIssuedProforma);
    }

    public function proforma(): BelongsTo
    {
        return $this->belongsTo(Proforma::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
