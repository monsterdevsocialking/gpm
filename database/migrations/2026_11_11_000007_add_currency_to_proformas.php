<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('proformas', 'currency')) {
            Schema::table('proformas', function (Blueprint $table): void {
                $table->char('currency', 3)->default('AOA')->after('total');
            });
        }
    }

    public function down(): void
    {
        // Retain currency values already used in issued commercial documents.
    }
};
