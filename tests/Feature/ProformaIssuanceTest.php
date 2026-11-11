<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Proforma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProformaIssuanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_issue_a_currency_aware_proforma_for_a_confirmed_order(): void
    {
        $customer = Customer::query()->create([
            'customer_type' => 'company',
            'name' => 'Cliente Proforma',
            'nif' => '987654321',
            'phone' => '+244900000003',
            'email' => 'proforma-customer@example.test',
            'password' => 'password',
            'address' => 'Rua Comercial',
            'city' => 'Benguela',
            'province' => 'Benguela',
            'active' => true,
        ]);

        $staff = User::query()->create([
            'name' => 'Vendedor Proforma',
            'email' => 'proforma-staff@example.test',
            'password' => 'password',
        ]);
        $staff->forceFill(['role' => 'seller', 'active' => true])->save();

        CompanySetting::query()->create([
            'company_name' => 'GPM Conecta',
            'nif' => '5000000000',
            'address' => 'Morada da GPM',
            'phone' => '+244900000004',
            'email' => 'comercial@gpm.example',
            'currency' => 'AOA',
            'delivery_fee' => 0,
        ]);

        $order = Order::query()->create([
            'order_number' => 'GPM-PROFORMA-0001',
            'customer_id' => $customer->id,
            'customer_type' => 'company',
            'customer_name' => $customer->name,
            'customer_nif' => $customer->nif,
            'customer_phone' => $customer->phone,
            'customer_email' => $customer->email,
            'delivery_address' => $customer->address,
            'subtotal' => 2500,
            'delivery_fee' => 100,
            'discount' => 50,
            'total' => 2550,
            'currency' => 'USD',
            'payment_status' => 'pending',
            'status' => 'confirmed',
            'stock_deducted' => false,
            'ordered_at' => now(),
            'confirmed_at' => now(),
        ]);

        $this->actingAs($staff, 'web')
            ->post(route('admin.orders.proformas.store', $order), [
                'valid_until' => today()->addDays(10)->toDateString(),
                'notes' => 'Condições comerciais acordadas.',
            ])
            ->assertRedirect();

        $proforma = Proforma::query()->where('order_id', $order->id)->firstOrFail();

        $this->assertStringStartsWith('PF-'.now()->format('Y').'-', $proforma->proforma_number);
        $this->assertSame('USD', $proforma->currency);
        $this->assertSame('GPM Conecta', $proforma->company_name);
        $this->assertSame('Cliente Proforma', $proforma->customer_name);
        $this->assertEquals(2550.0, (float) $proforma->total);

        $this->actingAs($staff, 'web')
            ->get(route('admin.proformas.show', $proforma))
            ->assertOk();

        $this->actingAs($customer, 'customer')
            ->get(route('customer.proformas.show', $proforma))
            ->assertOk();
    }
}
