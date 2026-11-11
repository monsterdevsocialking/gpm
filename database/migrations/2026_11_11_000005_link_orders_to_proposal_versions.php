<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'currency')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->char('currency', 3)->default('AOA')->after('total');
            });
        }

        if (! Schema::hasColumn('orders', 'proposal_version_id')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->foreignId('proposal_version_id')
                    ->nullable()
                    ->unique()
                    ->constrained('proposal_versions')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
            });
        }
    }

    public function down(): void
    {
        // Keep the source-proposal link and currency data on rollback.
        // These fields may already be used by confirmed business records.
    }
};
