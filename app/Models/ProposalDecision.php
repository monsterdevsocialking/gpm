<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class ProposalDecision extends Model
{
    protected $fillable = [
        'proposal_version_id', 'customer_id', 'decision', 'comment', 'decided_at',
    ];

    protected function casts(): array
    {
        return ['decided_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new LogicException('Uma decisão do cliente é um registo histórico e não pode ser alterada.');
        });

        static::deleting(function (): never {
            throw new LogicException('Uma decisão do cliente não pode ser eliminada.');
        });
    }

    public function proposalVersion(): BelongsTo
    {
        return $this->belongsTo(ProposalVersion::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
