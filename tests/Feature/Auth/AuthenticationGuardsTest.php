<?php

namespace Tests\Feature\Auth;

use App\Models\Customer;
use App\Models\User;
use Tests\TestCase;

class AuthenticationGuardsTest extends TestCase
{
    public function test_staff_and_customer_accounts_use_separate_guards(): void
    {
        $this->assertSame(User::class, config('auth.providers.users.model'));
        $this->assertSame(Customer::class, config('auth.providers.customers.model'));
        $this->assertSame('users', config('auth.guards.web.provider'));
        $this->assertSame('customers', config('auth.guards.customer.provider'));
    }
}
