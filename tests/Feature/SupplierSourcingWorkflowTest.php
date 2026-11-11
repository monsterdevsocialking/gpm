<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Models\QuoteRequestItem;
use App\Models\Supplier;
use App\Models\SupplierInquiry;
use App\Models\SupplierResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierSourcingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_record_a_supplier_inquiry_and_its_response(): void
    {
        $staff = User::query()->create([
            'name' => 'Gestor Comercial',
            'email' => 'sourcing-staff@example.test',
            'password' => 'password',
        ]);
        $staff->forceFill(['role' => 'admin', 'active' => true])->save();

        $customer = Customer::query()->create([
            'customer_type' => 'company',
            'name' => 'Empresa Compradora',
            'phone' => '+244900000020',
            'email' => 'sourcing-customer@example.test',
            'password' => 'password',
            'active' => true,
        ]);

        $request = QuoteRequest::query()->create([
            'request_number' => 'GPM-SOURCE-0001',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'status' => 'under_review',
            'submitted_at' => now(),
        ]);

        $item = QuoteRequestItem::query()->create([
            'quote_request_id' => $request->id,
            'product_name' => 'Gerador industrial',
            'product_sku' => 'GEN-100',
            'unit' => 'unidade',
            'quantity' => 2,
        ]);

        $supplier = Supplier::query()->create([
            'name' => 'Fornecedor de Teste',
            'contact_name' => 'Contacto Comercial',
            'email' => 'supplier@example.test',
            'phone' => '+244900000021',
            'country' => 'Angola',
            'active' => true,
        ]);

        $this->actingAs($staff, 'web')
            ->post(route('admin.requests.inquiries.store', $request), [
                'supplier_id' => $supplier->id,
                'reference' => 'CONS-TEST-01',
                'message' => 'Solicitamos cotação e prazo de entrega.',
                'response_due_at' => today()->addDays(3)->toDateString(),
            ])
            ->assertRedirect();

        $inquiry = SupplierInquiry::query()->firstOrFail();

        $this->assertSame('draft', $inquiry->status);
        $this->assertDatabaseHas('quote_requests', [
            'id' => $request->id,
            'status' => 'supplier_research',
        ]);

        $this->actingAs($staff, 'web')
            ->patch(route('admin.supplier-inquiries.mark-sent', $inquiry))
            ->assertRedirect();

        $this->assertSame('sent', $inquiry->fresh()->status);
        $this->assertNotNull($inquiry->fresh()->sent_at);

        $this->actingAs($staff, 'web')
            ->post(route('admin.supplier-inquiries.responses.store', $inquiry), [
                'supplier_reference' => 'SUP-QUOTE-500',
                'currency' => 'usd',
                'lead_time' => '10 dias úteis',
                'delivery_terms' => 'Entrega no armazém da GPM.',
                'payment_terms' => 'Pagamento por transferência.',
                'valid_until' => today()->addDays(10)->toDateString(),
                'items' => [
                    [
                        'quote_request_item_id' => $item->id,
                        'unit_price' => 125000,
                        'availability_status' => 'available',
                        'available_quantity' => 5,
                        'lead_time' => '10 dias úteis',
                        'conditions' => 'Preço válido para a quantidade indicada.',
                    ],
                ],
                'notes' => 'Resposta recebida por email.',
            ])
            ->assertRedirect();

        $inquiry->refresh();

        $this->assertSame('responded', $inquiry->status);
        $this->assertNotNull($inquiry->responded_at);

        $response = SupplierResponse::query()
            ->where('supplier_inquiry_id', $inquiry->id)
            ->firstOrFail();

        $this->assertSame('USD', $response->currency);
        $this->assertSame('received', $response->status);
        $this->assertDatabaseHas('supplier_response_items', [
            'supplier_response_id' => $response->id,
            'quote_request_item_id' => $item->id,
            'availability_status' => 'available',
        ]);
    }
}
