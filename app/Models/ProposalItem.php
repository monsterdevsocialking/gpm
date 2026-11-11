<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class ProposalItem extends Model
{
    protected $fillable = [
        'proposal_version_id', 'quote_request_item_id', 'product_id',
        'product_name', 'product_sku', 'unit', 'quantity',
        'unit_price', 'line_total', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        $guardVersion = function (self $item): void {
            $version = $item->proposalVersion()->first();

            if ($version && ! $version->isEditable()) {
                throw new LogicException('Não é permitido alterar artigos de uma proposta emitida. Crie uma nova versão.');
            }
        };

        static::creating($guardVersion);
        static::updating($guardVersion);
        static::deleting($guardVersion);
    }

    public function proposalVersion(): BelongsTo
    {
        return $this->belongsTo(ProposalVersion::class);
    }

    public function quoteRequestItem(): BelongsTo
    {
        return $this->belongsTo(QuoteRequestItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
