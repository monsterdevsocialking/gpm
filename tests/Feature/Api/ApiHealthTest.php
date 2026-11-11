<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class ApiHealthTest extends TestCase
{
    public function test_versioned_api_health_endpoint_returns_ok(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson([
                'application' => 'GPM Conecta',
                'status' => 'ok',
                'api_version' => 'v1',
            ]);
    }
}
