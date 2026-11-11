<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proforma_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('proforma_id')
                ->constrained('proformas')
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
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamp('created_at')->useCurrent();
            $table->index('proforma_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proforma_items');
    }
};
