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
        if (!Schema::hasColumn('masjids', 'adhan_audio')) {
            Schema::table('masjids', function (Blueprint $table) {
                $table->string('adhan_audio')->nullable()->after('video');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('masjids', 'adhan_audio')) {
            Schema::table('masjids', function (Blueprint $table) {
                $table->dropColumn('adhan_audio');
            });
        }
    }
};

