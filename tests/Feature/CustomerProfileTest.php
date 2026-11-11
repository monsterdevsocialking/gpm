<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_update_their_own_profile(): void
    {
        $customer = Customer::query()->create([
            'customer_type' => 'company',
            'name' => 'Empresa Original',
            'phone' => '+244900000030',
            'email' => 'account-original@example.test',
            'password' => 'initial-secret-123',
            'active' => true,
        ]);

        $this->actingAs($customer, 'customer')
            ->put(route('customer.profile.update'), [
                'name' => 'Empresa Actualizada',
                'email' => 'account-updated@example.test',
                'phone' => '+244900000031',
                'nif' => '500000002',
                'address' => 'Nova morada',
                'city' => 'Lobito',
                'province' => 'Benguela',
            ])
            ->assertRedirect(route('customer.profile.edit'));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Empresa Actualizada',
            'email' => 'account-updated@example.test',
            'phone' => '+244900000031',
            'address' => 'Nova morada',
        ]);
    }
}
