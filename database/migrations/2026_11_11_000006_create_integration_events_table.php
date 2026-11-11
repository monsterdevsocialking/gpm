<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_events', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 60);
            $table->string('event_id', 190);
            $table->string('event_type', 150);
            $table->string('aggregate_type', 80)->nullable();
            $table->unsignedBigInteger('aggregate_id')->nullable();
            $table->string('status', 20)->default('received')->index();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->json('payload')->nullable();
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'event_id'], 'integration_provider_event_unique');
            $table->index(['status', 'received_at'], 'integration_event_processing_index');
            $table->index(['aggregate_type', 'aggregate_id'], 'integration_event_aggregate_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_events');
    }
};
