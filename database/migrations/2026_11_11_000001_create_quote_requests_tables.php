<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('request_number', 40)->unique();
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->string('customer_name', 180);
            $table->string('customer_email', 190)->nullable();
            $table->string('customer_phone', 40);
            $table->string('status', 30)->default('submitted')->index();
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('quote_request_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quote_request_id')
                ->constrained('quote_requests')
                ->cascadeOnDelete()
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
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['quote_request_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_request_items');
        Schema::dropIfExists('quote_requests');
    }
};
