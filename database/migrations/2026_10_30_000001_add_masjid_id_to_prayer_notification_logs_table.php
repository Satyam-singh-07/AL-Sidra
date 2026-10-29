<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('prayer_notification_logs', function (Blueprint $table) {
            $table->dropUnique(['gregorian_date', 'prayer']);
            $table->foreignId('masjid_id')->nullable()->after('prayer')->constrained('masjids')->cascadeOnDelete();
            $table->unique(['gregorian_date', 'prayer', 'masjid_id'], 'unique_date_prayer_masjid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prayer_notification_logs', function (Blueprint $table) {
            $table->dropUnique('unique_date_prayer_masjid');
            $table->dropForeign(['masjid_id']);
            $table->dropColumn('masjid_id');
            $table->unique(['gregorian_date', 'prayer']);
        });
    }
};

