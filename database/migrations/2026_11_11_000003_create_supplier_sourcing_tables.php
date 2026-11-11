<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 180);
            $table->string('nif', 50)->nullable()->index();
            $table->string('contact_name', 150)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('website', 255)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('supplier_inquiries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quote_request_id')
                ->constrained('quote_requests')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('reference', 80)->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->text('message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('response_due_at')->nullable();
            $table->timestamps();

            $table->index(['quote_request_id', 'supplier_id']);
        });

        Schema::create('supplier_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_inquiry_id')
                ->constrained('supplier_inquiries')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('supplier_reference', 100)->nullable();
            $table->string('status', 30)->default('received')->index();
            $table->char('currency', 3)->nullable();
            $table->string('lead_time', 180)->nullable();
            $table->text('delivery_terms')->nullable();
            $table->text('payment_terms')->nullable();
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        Schema::create('supplier_response_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_response_id')
                ->constrained('supplier_responses')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('quote_request_item_id')
                ->constrained('quote_request_items')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->string('availability_status', 30)->default('unknown');
            $table->decimal('available_quantity', 15, 3)->nullable();
            $table->string('lead_time', 180)->nullable();
            $table->text('conditions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['supplier_response_id', 'quote_request_item_id'], 'supplier_response_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_response_items');
        Schema::dropIfExists('supplier_responses');
        Schema::dropIfExists('supplier_inquiries');
        Schema::dropIfExists('suppliers');
    }
};
