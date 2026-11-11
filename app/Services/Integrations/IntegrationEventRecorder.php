<?php

namespace App\Services\Integrations;

use App\Models\IntegrationEvent;
use InvalidArgumentException;

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
        $provider = strtolower(trim($provider));
        $eventId = trim($eventId);
        $eventType = trim($eventType);

        if ($provider === '' || $eventId === '' || $eventType === '') {
            throw new InvalidArgumentException('Provider, event ID and event type are required.');
        }

        if (mb_strlen($provider) > 60 || mb_strlen($eventId) > 190 || mb_strlen($eventType) > 150) {
            throw new InvalidArgumentException('Provider event identifiers exceed the supported length.');
        }

        return IntegrationEvent::query()->firstOrCreate(
            [
                'provider' => $provider,
                'event_id' => $eventId,
            ],
            [
                'event_type' => $eventType,
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
