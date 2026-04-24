<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->foreignId('masjid_id')->nullable()->after('member_category_id')->constrained('masjids')->nullOnDelete();
            $table->foreignId('madarsa_id')->nullable()->after('masjid_id')->constrained('madarsas')->nullOnDelete();
        });

        // Migrate existing data
        DB::table('member_profiles')->where('place_type', 'masjid')->update([
            'masjid_id' => DB::raw('place_id')
        ]);
        
        DB::table('member_profiles')->where('place_type', 'madarsa')->update([
            'madarsa_id' => DB::raw('place_id')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropForeign(['masjid_id']);
            $table->dropForeign(['madarsa_id']);
            $table->dropColumn(['masjid_id', 'madarsa_id']);
        });
    }
};
