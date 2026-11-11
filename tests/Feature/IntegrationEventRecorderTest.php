<?php

namespace Tests\Feature;

use App\Models\IntegrationEvent;
use App\Services\Integrations\IntegrationEventRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationEventRecorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_provider_delivery_is_recorded_only_once(): void
    {
        $recorder = app(IntegrationEventRecorder::class);

        $first = $recorder->record(
            'ExamplePay',
            'evt_123',
            'payment.confirmed',
            ['reference' => 'GPM-ORDER-123']
        );

        $duplicate = $recorder->record(
            'examplepay',
            'evt_123',
            'payment.confirmed',
            ['reference' => 'different-payload']
        );

        $this->assertSame($first->id, $duplicate->id);
        $this->assertSame(1, IntegrationEvent::query()->count());
        $this->assertSame(['reference' => 'GPM-ORDER-123'], $duplicate->payload);
        $this->assertSame('received', $duplicate->status);
    }
}
