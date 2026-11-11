<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contact_messages')) {
            return;
        }

        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 190);
            $table->string('phone', 40)->nullable();
            $table->string('subject', 180)->nullable();
            $table->text('message');
            $table->timestamp('created_at')->useCurrent();
            $table->index('created_at', 'idx_contact_messages_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
