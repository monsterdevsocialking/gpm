<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LogicException;

class ProposalVersion extends Model
{
    protected $fillable = [
        'proposal_id', 'version_number', 'status', 'currency', 'subtotal',
        'valid_until', 'terms', 'notes', 'issued_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'valid_until' => 'date',
            'issued_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (self $version): void {
            if ($version->getOriginal('status') !== 'draft') {
                $dirtyFields = array_diff(
                    array_keys($version->getDirty()),
                    ['updated_at']
                );

                if ($dirtyFields !== []) {
                    throw new LogicException('Uma versão emitida de proposta é imutável. Crie uma nova versão.');
                }
            }
        });

        static::deleting(function (self $version): void {
            if (! $version->isEditable()) {
                throw new LogicException('Uma versão emitida de proposta não pode ser eliminada.');
            }
        });
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProposalItem::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(ProposalDecision::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft' && $this->issued_at === null;
    }
}
