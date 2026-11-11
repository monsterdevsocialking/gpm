<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_request_status_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quote_request_id')
                ->constrained('quote_requests')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['quote_request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_request_status_history');
    }
};
