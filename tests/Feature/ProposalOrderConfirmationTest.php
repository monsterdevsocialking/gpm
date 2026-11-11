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

        $this->actingAs($staff, 'web')
            ->patch(route('admin.orders.status.update', $order), ['status' => 'preparing'])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->actingAs($staff, 'web')
            ->patch(route('admin.orders.status.update', $order), ['status' => 'shipped'])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->actingAs($staff, 'web')
            ->patch(route('admin.orders.status.update', $order), ['status' => 'completed'])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame('completed', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->completed_at);
        $this->assertSame(4, $order->statusHistory()->count());
    }

    public function test_revision_request_returns_proposal_to_preparation_and_allows_a_new_version(): void
    {
        $customer = Customer::query()->create([
            'customer_type' => 'company',
            'name' => 'Empresa de Revisão',
            'phone' => '+244900000011',
            'email' => 'revision-customer@example.test',
            'password' => 'password',
            'active' => true,
        ]);

        $staff = User::query()->create([
            'name' => 'Comercial de Teste',
            'email' => 'revision-staff@example.test',
            'password' => 'password',
        ]);
        $staff->forceFill(['role' => 'admin', 'active' => true])->save();

        $request = QuoteRequest::query()->create([
            'request_number' => 'GPM-REVISION-0001',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'status' => 'proposal_sent',
            'submitted_at' => now(),
        ]);

        $requestItem = QuoteRequestItem::query()->create([
            'quote_request_id' => $request->id,
            'product_name' => 'Equipamento industrial',
            'product_sku' => 'EQ-REV-01',
            'unit' => 'unidade',
            'quantity' => 1,
        ]);

        $proposal = Proposal::query()->create([
            'quote_request_id' => $request->id,
            'proposal_number' => 'GPM-PROP-REV-0001',
            'status' => 'sent',
            'created_by' => $staff->id,
        ]);

        $version = ProposalVersion::query()->create([
            'proposal_id' => $proposal->id,
            'version_number' => 1,
            'status' => 'sent',
            'currency' => 'AOA',
            'subtotal' => 1000,
            'valid_until' => today()->addDays(7),
            'issued_at' => now(),
            'created_by' => $staff->id,
        ]);

        $version->items()->create([
            'quote_request_item_id' => $requestItem->id,
            'product_name' => $requestItem->product_name,
            'product_sku' => $requestItem->product_sku,
            'unit' => $requestItem->unit,
            'quantity' => 1,
            'unit_price' => 1000,
            'line_total' => 1000,
        ]);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.proposals.decide', $version), [
                'decision' => 'revision_requested',
                'comment' => 'Solicito uma alternativa com prazo de entrega menor.',
            ])
            ->assertRedirect(route('customer.proposals.show', $version));

        $this->assertDatabaseHas('quote_requests', [
            'id' => $request->id,
            'status' => 'proposal_preparation',
        ]);
        $this->assertDatabaseHas('quote_request_status_history', [
            'quote_request_id' => $request->id,
            'old_status' => 'proposal_sent',
            'new_status' => 'proposal_preparation',
        ]);

        $this->actingAs($staff, 'web')
            ->post(route('admin.requests.proposals.store', $request))
            ->assertRedirect();

        $newVersion = ProposalVersion::query()
            ->where('proposal_id', $proposal->id)
            ->orderByDesc('version_number')
            ->firstOrFail();

        $this->assertSame(2, $newVersion->version_number);
        $this->assertSame('draft', $newVersion->status);
        $this->assertCount(1, $newVersion->items);
        $this->assertSame('Equipamento industrial', $newVersion->items->first()->product_name);
    }
}
