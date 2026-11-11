<?php

namespace Tests\Unit\Models;

use App\Models\ProposalVersion;
use PHPUnit\Framework\TestCase;

class ProposalVersionTest extends TestCase
{
    public function test_new_draft_version_is_editable(): void
    {
        $version = new ProposalVersion([
            'status' => 'draft',
            'issued_at' => null,
        ]);

        $this->assertTrue($version->isEditable());
    }

    public function test_issued_version_is_not_editable(): void
    {
        $version = new ProposalVersion([
            'status' => 'sent',
            'issued_at' => '2026-11-11 10:00:00',
        ]);

        $this->assertFalse($version->isEditable());
    }

    public function test_draft_with_an_issue_timestamp_is_not_editable(): void
    {
        $version = new ProposalVersion([
            'status' => 'draft',
            'issued_at' => '2026-11-11 10:00:00',
        ]);

        $this->assertFalse($version->isEditable());
    }
}
