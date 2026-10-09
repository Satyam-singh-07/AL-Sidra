<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mohalla_mutawallis', function (Blueprint $table) {
            $table->foreignId('mohalla_id')->nullable()->after('user_id')->constrained('mohallas')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('mohalla_mutawallis', function (Blueprint $table) {
            $table->dropForeign(['mohalla_id']);
            $table->dropColumn('mohalla_id');
        });
    }
};
