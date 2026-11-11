<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Proposal;
use App\Models\ProposalVersion;
use App\Models\QuoteRequest;
use App\Models\QuoteRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProposalOrderConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_acceptance_does_not_create_an_order_until_staff_confirms_it(): void
    {
        $customer = Customer::query()->create([
            'customer_type' => 'company',
            'name' => 'Empresa de Teste',
            'nif' => '123456789',
            'phone' => '+244900000000',
            'email' => 'cliente@example.test',
            'password' => 'password',
            'address' => 'Rua de Teste',
            'city' => 'Benguela',
            'province' => 'Benguela',
            'active' => true,
        ]);

        $staff = User::query()->create([
            'name' => 'Administrador de Teste',
            'email' => 'admin@example.test',
            'password' => 'password',
        ]);
        $staff->forceFill(['role' => 'admin', 'active' => true])->save();

        $request = QuoteRequest::query()->create([
            'request_number' => 'GPM-TEST-0001',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'status' => 'proposal_sent',
            'submitted_at' => now(),
        ]);

        $requestItem = QuoteRequestItem::query()->create([
            'quote_request_id' => $request->id,
            'product_name' => 'Material de teste',
            'product_sku' => 'TEST-01',
            'unit' => 'unidade',
            'quantity' => 2,
        ]);

        $proposal = Proposal::query()->create([
            'quote_request_id' => $request->id,
            'proposal_number' => 'GPM-PROP-TEST-0001',
            'status' => 'sent',
            'created_by' => $staff->id,
        ]);

        $version = ProposalVersion::query()->create([
            'proposal_id' => $proposal->id,
            'version_number' => 1,
            'status' => 'draft',
            'currency' => 'AOA',
            'subtotal' => 2000,
            'valid_until' => today()->addDays(7),
            'issued_at' => null,
            'created_by' => $staff->id,
        ]);

        $version->items()->create([
            'quote_request_item_id' => $requestItem->id,
            'product_name' => 'Material de teste',
            'product_sku' => 'TEST-01',
            'unit' => 'unidade',
            'quantity' => 2,
            'unit_price' => 1000,
            'line_total' => 2000,
        ]);

        $version->update(['status' => 'sent', 'issued_at' => now()]);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.proposals.decide', $version), [
                'decision' => 'accepted',
                'comment' => 'Proposta aceite para teste.',
            ])
            ->assertRedirect(route('customer.proposals.show', $version));

        $this->assertDatabaseHas('proposal_decisions', [
            'proposal_version_id' => $version->id,
            'customer_id' => $customer->id,
            'decision' => 'accepted',
        ]);

        $this->assertDatabaseMissing('orders', [
            'proposal_version_id' => $version->id,
        ]);

        $this->actingAs($staff, 'web')
            ->post(route('admin.proposals.confirm-order', $version))
            ->assertRedirect();

        $order = Order::query()
            ->where('proposal_version_id', $version->id)
            ->firstOrFail();

        $this->assertSame('confirmed', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('AOA', $order->currency);
        $this->assertFalse($order->stock_deducted);
        $this->assertEquals(2000.0, (float) $order->total);

        $orderItem = $order->items()->firstOrFail();
        $this->assertSame('Material de teste', $orderItem->product_name);
        $this->assertEquals(2.0, (float) $orderItem->quantity);
        $this->assertEquals(2000.0, (float) $orderItem->subtotal);

        $this->assertDatabaseHas('quote_requests', [
            'id' => $request->id,
            'status' => 'closed',
        ]);

        $this->actingAs($staff, 'web')
            ->post(route('admin.proposals.confirm-order', $version))
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame(
            1,
            Order::query()->where('proposal_version_id', $version->id)->count()
        );
    }
}
