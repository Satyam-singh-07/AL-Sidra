<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('religion_infos', function (Blueprint $blueprint) {
            $blueprint->string('serial_number')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('religion_infos', function (Blueprint $blueprint) {
            $blueprint->dropColumn('serial_number');
        });
    }
};
