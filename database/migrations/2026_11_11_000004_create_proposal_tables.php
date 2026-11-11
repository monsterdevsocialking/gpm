<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quote_request_id')
                ->unique()
                ->constrained('quote_requests')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->string('proposal_number', 50)->unique();
            $table->string('status', 30)->default('draft')->index();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
        });

        Schema::create('proposal_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('proposal_id')
                ->constrained('proposals')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->unsignedInteger('version_number');
            $table->string('status', 30)->default('draft')->index();
            $table->char('currency', 3)->nullable();
            $table->decimal('subtotal', 15, 2)->nullable();
            $table->date('valid_until')->nullable();
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();

            $table->unique(['proposal_id', 'version_number'], 'proposal_version_unique');
        });

        Schema::create('proposal_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('proposal_version_id')
                ->constrained('proposal_versions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('quote_request_item_id')
                ->nullable()
                ->constrained('quote_request_items')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('product_name', 200);
            $table->string('product_sku', 80)->nullable();
            $table->string('unit', 40)->default('unidade');
            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->decimal('line_total', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('proposal_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('proposal_version_id')
                ->constrained('proposal_versions')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->string('decision', 30);
            $table->text('comment')->nullable();
            $table->timestamp('decided_at');
            $table->timestamps();

            $table->unique(['proposal_version_id', 'customer_id'], 'proposal_decision_version_customer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_decisions');
        Schema::dropIfExists('proposal_items');
        Schema::dropIfExists('proposal_versions');
        Schema::dropIfExists('proposals');
    }
};
