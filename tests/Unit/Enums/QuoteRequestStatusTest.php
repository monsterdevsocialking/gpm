<?php

namespace Tests\Unit\Enums;

use App\Enums\QuoteRequestStatus;
use PHPUnit\Framework\TestCase;

class QuoteRequestStatusTest extends TestCase
{
    public function test_supply_request_statuses_have_explicit_values_and_labels(): void
    {
        $this->assertSame([
            'submitted',
            'under_review',
            'supplier_research',
            'proposal_preparation',
            'proposal_sent',
            'closed',
            'cancelled',
        ], QuoteRequestStatus::values());

        $this->assertSame('Recebido', QuoteRequestStatus::Submitted->label());
        $this->assertSame('Consulta a fornecedores', QuoteRequestStatus::SupplierResearch->label());
    }
}
