<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 120);
                $table->string('slug', 150)->unique();
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->boolean('active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('company_settings')) {
            Schema::create('company_settings', function (Blueprint $table): void {
                $table->id();
                $table->string('company_name', 180)->default('GPM');
                $table->string('nif', 50)->nullable();
                $table->text('address')->nullable();
                $table->string('city', 100)->nullable();
                $table->string('province', 100)->nullable();
                $table->string('phone', 40)->nullable();
                $table->string('email', 190)->nullable();
                $table->string('logo_path')->nullable();
                $table->char('currency', 3)->default('AOA');
                $table->decimal('delivery_fee', 15, 2)->default(0);
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
        }

        if (! Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table): void {
                $table->id();
                $table->string('customer_type', 20)->default('individual');
                $table->string('name', 180);
                $table->string('nif', 50)->nullable()->index();
                $table->string('phone', 40)->index();
                $table->string('email', 190)->nullable()->index();
                $table->string('password')->nullable();
                $table->text('address')->nullable();
                $table->string('city', 100)->nullable();
                $table->string('province', 100)->nullable();
                $table->boolean('active')->default(true);
                $table->dateTime('email_verified_at')->nullable();
                $table->dateTime('last_login_at')->nullable();
                $table->timestamps();
                $table->index('name');
            });
        }

        if (! Schema::hasTable('customer_addresses')) {
            Schema::create('customer_addresses', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('label', 100)->nullable();
                $table->text('address')->nullable();
                $table->string('street', 180)->nullable();
                $table->string('neighborhood', 120)->nullable();
                $table->string('reference_point')->nullable();
                $table->string('city', 100)->nullable();
                $table->string('province', 100)->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete()->cascadeOnUpdate();
                $table->string('name', 200);
                $table->string('slug', 220)->unique();
                $table->string('sku', 80)->nullable()->unique();
                $table->longText('description')->nullable();
                $table->decimal('price', 15, 2)->default(0);
                $table->decimal('cost_price', 15, 2)->nullable();
                $table->decimal('stock_quantity', 15, 3)->default(0);
                $table->decimal('min_stock', 15, 3)->default(0);
                $table->string('unit', 40)->default('unidade');
                $table->string('main_image')->nullable();
                $table->boolean('active')->default(true);
                $table->boolean('featured')->default(false);
                $table->timestamps();
                $table->index('name');
                $table->index(['category_id', 'active']);
                $table->index('stock_quantity');
                $table->index(['featured', 'active']);
            });
        }

        if (! Schema::hasTable('product_images')) {
            Schema::create('product_images', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete()->cascadeOnUpdate();
                $table->string('image_path');
                $table->string('alt_text', 180)->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamp('created_at')->useCurrent();
                $table->index(['product_id', 'sort_order']);
            });
        }

        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table): void {
                $table->id();
                $table->string('order_number', 40)->unique();
                $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete()->cascadeOnUpdate();
                $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
                $table->string('customer_type', 20)->default('individual');
                $table->string('customer_name', 180);
                $table->string('customer_nif', 50)->nullable();
                $table->string('customer_phone', 40);
                $table->string('customer_email', 190)->nullable();
                $table->text('delivery_address')->nullable();
                $table->string('delivery_city', 100)->nullable();
                $table->string('delivery_province', 100)->nullable();
                $table->decimal('delivery_fee', 15, 2)->default(0);
                $table->decimal('discount', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->char('currency', 3)->default('AOA');
                $table->string('payment_method', 80)->nullable();
                $table->string('payment_status', 20)->default('pending');
                $table->string('status', 30)->default('pending')->index();
                $table->text('notes')->nullable();
                $table->boolean('stock_deducted')->default(false);
                $table->dateTime('ordered_at')->useCurrent();
                $table->dateTime('confirmed_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->dateTime('cancelled_at')->nullable();
                $table->timestamps();
                $table->string('delivery_street', 180)->nullable();
                $table->string('delivery_neighborhood', 120)->nullable();
                $table->string('delivery_reference')->nullable();
                $table->index(['customer_id', 'ordered_at']);
                $table->index(['status', 'ordered_at']);
                $table->index('responsible_user_id');
            });
        }

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete()->cascadeOnUpdate();
                $table->string('product_name', 200);
                $table->string('product_sku', 80)->nullable();
                $table->string('unit', 40)->default('unidade');
                $table->decimal('quantity', 15, 3);
                $table->decimal('unit_price', 15, 2);
                $table->decimal('subtotal', 15, 2);
                $table->timestamp('created_at')->useCurrent();
                $table->index('product_id');
            });
        }

        if (! Schema::hasTable('order_status_history')) {
            Schema::create('order_status_history', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
                $table->string('old_status', 30)->nullable();
                $table->string('new_status', 30);
                $table->text('note')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['order_id', 'created_at']);
                $table->index('user_id');
            });
        }

        if (! Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete()->cascadeOnUpdate();
                $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
                $table->string('status', 20)->default('open');
                $table->dateTime('last_message_at')->nullable();
                $table->timestamps();
                $table->index('customer_id');
                $table->index('assigned_user_id');
                $table->index('last_message_at');
            });
        }

        if (! Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete()->cascadeOnUpdate();
                $table->string('sender_type', 20);
                $table->foreignId('customer_sender_id')->nullable()->constrained('customers')->nullOnDelete()->cascadeOnUpdate();
                $table->foreignId('user_sender_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
                $table->text('body');
                $table->dateTime('read_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['conversation_id', 'created_at']);
                $table->index(['conversation_id', 'read_at']);
            });
        }

        if (! Schema::hasTable('proformas')) {
            Schema::create('proformas', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->restrictOnDelete()->cascadeOnUpdate();
                $table->string('proforma_number', 50)->unique();
                $table->string('company_name', 180)->nullable();
                $table->string('company_nif', 50)->nullable();
                $table->text('company_address')->nullable();
                $table->string('company_phone', 40)->nullable();
                $table->string('company_email', 190)->nullable();
                $table->string('customer_name', 180);
                $table->string('customer_nif', 50)->nullable();
                $table->string('customer_phone', 40)->nullable();
                $table->string('customer_email', 190)->nullable();
                $table->text('customer_address')->nullable();
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('delivery_fee', 15, 2)->default(0);
                $table->decimal('discount', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->char('currency', 3)->default('AOA');
                $table->string('status', 20)->default('issued');
                $table->date('issue_date');
                $table->date('valid_until')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
                $table->timestamps();
                $table->index('issue_date');
                $table->index('status');
            });
        }

        if (! Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete()->cascadeOnUpdate();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete()->cascadeOnUpdate();
                $table->string('movement_type', 30);
                $table->decimal('quantity', 15, 3);
                $table->decimal('stock_before', 15, 3);
                $table->decimal('stock_after', 15, 3);
                $table->string('reason')->nullable();
                $table->string('reference', 100)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['product_id', 'created_at']);
                $table->index('order_id');
                $table->index('movement_type');
            });
        }

        if (! Schema::hasTable('activity_log')) {
            Schema::create('activity_log', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
                $table->string('action', 100);
                $table->string('entity_type', 100)->nullable();
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->text('description')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['user_id', 'created_at']);
                $table->index(['entity_type', 'entity_id']);
            });
        }

        if (! Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 150);
                $table->string('email', 190);
                $table->string('phone', 40)->nullable();
                $table->string('subject', 180)->nullable();
                $table->text('message');
                $table->timestamp('created_at')->useCurrent();
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        // This baseline intentionally does not drop legacy business tables.
        // They may contain imported production data and must be retained on rollback.
    }
};
