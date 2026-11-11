<?php

namespace App\Services\Integrations;

use App\Models\IntegrationEvent;

class IntegrationEventRecorder
{
    /**
     * Persist a provider event once. Repeated deliveries return the original record.
     *
     * Raw payloads must be sanitized before recording: never persist card data,
     * authentication secrets, API keys, or webhook signatures.
     */
    public function record(
        string $provider,
        string $eventId,
        string $eventType,
        array $payload = [],
        ?string $aggregateType = null,
        ?int $aggregateId = null,
    ): IntegrationEvent {
        return IntegrationEvent::query()->firstOrCreate(
            [
                'provider' => strtolower(trim($provider)),
                'event_id' => trim($eventId),
            ],
            [
                'event_type' => trim($eventType),
                'aggregate_type' => $aggregateType,
                'aggregate_id' => $aggregateId,
                'status' => 'received',
                'attempts' => 0,
                'payload' => $payload,
                'received_at' => now(),
            ]
        );
    }
}
