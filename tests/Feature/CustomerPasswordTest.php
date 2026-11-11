<?php

namespace Tests\\Feature;

use App\\Models\\Customer;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Illuminate\\Support\\Facades\\Hash;
use Tests\\TestCase;

class CustomerPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_change_password_with_the_current_password(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer, 'customer')
            ->put(route('customer.password.update'), [
                'current_password' => 'current-secret-123',
                'password' => 'new-secret-456',
                'password_confirmation' => 'new-secret-456',
            ])
            ->assertRedirect(route('customer.password.edit'))
            ->assertSessionHasNoErrors();

        $customer->refresh();

        $this->assertTrue(Hash::check('new-secret-456', $customer->password));
        $this->assertFalse(Hash::check('current-secret-123', $customer->password));
    }

    public function test_customer_cannot_change_password_with_an_incorrect_current_password(): void
    {
        $customer = $this->createCustomer();
        $originalHash = $customer->password;

        $this->actingAs($customer, 'customer')
            ->from(route('customer.password.edit'))
            ->put(route('customer.password.update'), [
                'current_password' => 'wrong-current-password',
                'password' => 'new-secret-456',
                'password_confirmation' => 'new-secret-456',
            ])
            ->assertRedirect(route('customer.password.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertSame($originalHash, $customer->fresh()->password);
    }

    public function test_customer_must_confirm_the_new_password(): void
    {
        $customer = $this->createCustomer();
        $originalHash = $customer->password;

        $this->actingAs($customer, 'customer')
            ->from(route('customer.password.edit'))
            ->put(route('customer.password.update'), [
                'current_password' => 'current-secret-123',
                'password' => 'new-secret-456',
                'password_confirmation' => 'different-secret-789',
            ])
            ->assertRedirect(route('customer.password.edit'))
            ->assertSessionHasErrors('password');

        $this->assertSame($originalHash, $customer->fresh()->password);
    }

    private function createCustomer(): Customer
    {
        return Customer::query()->create([
            'customer_type' => 'company',
            'name' => 'Empresa com Conta',
            'phone' => '+244900000040',
            'email' => 'password-customer@example.test',
            'password' => 'current-secret-123',
            'active' => true,
        ]);
    }
}
