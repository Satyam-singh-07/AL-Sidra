<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE muqquir_availabilities
            MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'available'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE muqquir_availabilities
            MODIFY COLUMN status VARCHAR(10) NOT NULL DEFAULT 'available'
        ");
    }
};

