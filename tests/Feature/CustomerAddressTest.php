<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_add_multiple_addresses_and_first_one_becomes_default(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer, 'customer')
            ->post(route('customer.addresses.store'), [
                'label' => 'Armazém',
                'address' => 'Rua da Liberdade, n.º 10',
                'city' => 'Lobito',
                'province' => 'Benguela',
            ])
            ->assertRedirect(route('customer.addresses.index'));

        $first = $customer->addresses()->firstOrFail();
        $this->assertTrue($first->is_default);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.addresses.store'), [
                'label' => 'Escritório',
                'street' => 'Avenida 4 de Fevereiro',
                'neighborhood' => 'Centro',
                'city' => 'Benguela',
                'province' => 'Benguela',
            ])
            ->assertRedirect(route('customer.addresses.index'));

        $this->assertSame(2, $customer->addresses()->count());
        $this->assertTrue($first->fresh()->is_default);
    }

    public function test_customer_can_change_default_address_and_default_is_reassigned_when_deleted(): void
    {
        $customer = $this->createCustomer();
        $first = $customer->addresses()->create([
            'label' => 'Sede',
            'address' => 'Rua A',
            'is_default' => true,
        ]);
        $second = $customer->addresses()->create([
            'label' => 'Armazém',
            'address' => 'Rua B',
        ]);

        $this->actingAs($customer, 'customer')
            ->patch(route('customer.addresses.default', $second))
            ->assertRedirect(route('customer.addresses.index'));

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);

        $this->actingAs($customer, 'customer')
            ->delete(route('customer.addresses.destroy', $second))
            ->assertRedirect(route('customer.addresses.index'));

        $this->assertTrue($first->fresh()->is_default);
    }

    public function test_customer_cannot_edit_or_delete_another_customers_address(): void
    {
        $customer = $this->createCustomer();
        $otherCustomer = $this->createCustomer('other-address-owner@example.test');
        $address = $otherCustomer->addresses()->create([
            'label' => 'Privada',
            'address' => 'Morada confidencial',
        ]);

        $this->actingAs($customer, 'customer')
            ->put(route('customer.addresses.update', $address), [
                'label' => 'Alterada',
                'address' => 'Tentativa de alteração',
            ])
            ->assertNotFound();

        $this->actingAs($customer, 'customer')
            ->delete(route('customer.addresses.destroy', $address))
            ->assertNotFound();

        $this->assertSame('Morada confidencial', $address->fresh()->address);
    }

    private function createCustomer(string $email = 'address-customer@example.test'): Customer
    {
        return Customer::query()->create([
            'customer_type' => 'company',
            'name' => 'Empresa com Moradas',
            'phone' => '+244900000050',
            'email' => $email,
            'password' => 'address-secret-123',
            'active' => true,
        ]);
    }
}
