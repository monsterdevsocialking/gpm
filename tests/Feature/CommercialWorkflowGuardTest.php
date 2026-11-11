<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Proposal;
use App\Models\ProposalDecision;
use App\Models\ProposalVersion;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class CommercialWorkflowGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_generic_status_endpoint_cannot_close_a_request_awaiting_confirmation(): void
    {
        $staff = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@gpm.test',
            'password' => 'password',
        ]);
        $staff->forceFill(['role' => 'admin', 'active' => true])->save();

        $customer = Customer::query()->create([
            'customer_type' => 'company',
            'name' => 'Empresa de Teste',
            'phone' => '900000000',
            'email' => 'cliente@gpm.test',
            'password' => 'password',
            'active' => true,
        ]);

        $request = QuoteRequest::query()->create([
            'request_number' => 'GPM-TEST-0001',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'status' => 'proposal_sent',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($staff, 'web')
            ->from(route('admin.requests.show', $request))
            ->patch(route('admin.requests.status.update', $request), [
                'status' => 'closed',
                'note' => 'Tentativa de fecho sem confirmação comercial.',
            ]);

        $response->assertRedirect(route('admin.requests.show', $request));
        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('quote_requests', [
            'id' => $request->id,
            'status' => 'proposal_sent',
        ]);
    }

    public function test_issued_proposal_version_cannot_be_changed(): void
    {
        [$customer, $request] = $this->createCustomerAndRequest();

        $proposal = Proposal::query()->create([
            'quote_request_id' => $request->id,
            'proposal_number' => 'GPM-PROP-TEST-0001',
            'status' => 'sent',
        ]);

        $version = ProposalVersion::query()->create([
            'proposal_id' => $proposal->id,
            'version_number' => 1,
            'status' => 'sent',
            'currency' => 'AOA',
            'subtotal' => 1000,
            'valid_until' => today()->addDays(7),
            'issued_at' => now(),
        ]);

        $this->expectException(LogicException::class);

        $version->update(['notes' => 'Tentativa de alterar versão emitida.']);
    }


    public function test_draft_with_an_issue_timestamp_is_also_immutable(): void
    {
        [, $request] = $this->createCustomerAndRequest();

        $proposal = Proposal::query()->create([
            'quote_request_id' => $request->id,
            'proposal_number' => 'GPM-PROP-TEST-0003',
            'status' => 'draft',
        ]);

        $version = ProposalVersion::query()->create([
            'proposal_id' => $proposal->id,
            'version_number' => 1,
            'status' => 'draft',
            'currency' => 'AOA',
            'subtotal' => 1000,
            'valid_until' => today()->addDays(7),
            'issued_at' => now(),
        ]);

        $this->expectException(LogicException::class);

        $version->update(['notes' => 'Tentativa de alterar uma versão marcada como emitida.']);
    }


    public function test_customer_decision_is_an_immutable_historical_record(): void
    {
        [$customer, $request] = $this->createCustomerAndRequest();

        $proposal = Proposal::query()->create([
            'quote_request_id' => $request->id,
            'proposal_number' => 'GPM-PROP-TEST-0002',
            'status' => 'accepted',
        ]);

        $version = ProposalVersion::query()->create([
            'proposal_id' => $proposal->id,
            'version_number' => 1,
            'status' => 'sent',
            'currency' => 'AOA',
            'subtotal' => 1000,
            'valid_until' => today()->addDays(7),
            'issued_at' => now(),
        ]);

        $decision = ProposalDecision::query()->create([
            'proposal_version_id' => $version->id,
            'customer_id' => $customer->id,
            'decision' => 'accepted',
            'decided_at' => now(),
        ]);

        $this->expectException(LogicException::class);

        $decision->update(['decision' => 'rejected']);
    }

    /**
     * @return array{0: Customer, 1: QuoteRequest}
     */
    private function createCustomerAndRequest(): array
    {
        $customer = Customer::query()->create([
            'customer_type' => 'company',
            'name' => 'Empresa de Teste',
            'phone' => '900000000',
            'email' => 'cliente'.fake()->unique()->numberBetween(1, 999999).'@gpm.test',
            'password' => 'password',
            'active' => true,
        ]);

        $request = QuoteRequest::query()->create([
            'request_number' => 'GPM-TEST-'.fake()->unique()->numerify('####'),
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'status' => 'proposal_sent',
            'submitted_at' => now(),
        ]);

        return [$customer, $request];
    }
}
