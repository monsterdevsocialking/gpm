<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationEvent extends Model
{
    protected $fillable = [
        'provider',
        'event_id',
        'event_type',
        'aggregate_type',
        'aggregate_id',
        'status',
        'attempts',
        'payload',
        'received_at',
        'processed_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'aggregate_id' => 'integer',
            'attempts' => 'integer',
            'payload' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function markProcessed(): void
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
            'last_error' => null,
        ]);
    }

    public function markFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'attempts' => $this->attempts + 1,
            'last_error' => mb_substr($reason, 0, 5000),
        ]);
    }
}
