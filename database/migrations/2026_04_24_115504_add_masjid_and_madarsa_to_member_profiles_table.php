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
        // 1. Ensure columns exist
        Schema::table('member_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('member_profiles', 'masjid_id')) {
                $table->unsignedBigInteger('masjid_id')->nullable()->after('member_category_id');
            }
            if (!Schema::hasColumn('member_profiles', 'madarsa_id')) {
                $table->unsignedBigInteger('madarsa_id')->nullable()->after('masjid_id');
            }
        });

        // 2. Temporarily drop foreign keys if they exist to allow data cleanup/migration
        $databaseName = DB::connection()->getDatabaseName();
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = '$databaseName' 
            AND TABLE_NAME = 'member_profiles' 
            AND CONSTRAINT_NAME IN ('member_profiles_masjid_id_foreign', 'member_profiles_madarsa_id_foreign')
        ");
        $names = array_column($foreignKeys, 'CONSTRAINT_NAME');

        Schema::table('member_profiles', function (Blueprint $table) use ($names) {
            if (in_array('member_profiles_masjid_id_foreign', $names)) {
                $table->dropForeign(['masjid_id']);
            }
            if (in_array('member_profiles_madarsa_id_foreign', $names)) {
                $table->dropForeign(['madarsa_id']);
            }
        });

        // 3. Migrate existing data defensively (only if target exists)
        DB::statement("
            UPDATE member_profiles 
            SET masjid_id = place_id 
            WHERE place_type = 'masjid' 
            AND place_id IS NOT NULL 
            AND place_id IN (SELECT id FROM masjids)
        ");

        DB::statement("
            UPDATE member_profiles 
            SET madarsa_id = place_id 
            WHERE place_type = 'madarsa' 
            AND place_id IS NOT NULL 
            AND place_id IN (SELECT id FROM madarsas)
        ");

        // 4. (Re)add foreign key constraints
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->foreign('masjid_id')->references('id')->on('masjids')->nullOnDelete();
            $table->foreign('madarsa_id')->references('id')->on('madarsas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $databaseName = DB::connection()->getDatabaseName();
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = '$databaseName' 
                AND TABLE_NAME = 'member_profiles' 
                AND CONSTRAINT_NAME IN ('member_profiles_masjid_id_foreign', 'member_profiles_madarsa_id_foreign')
            ");
            $names = array_column($foreignKeys, 'CONSTRAINT_NAME');

            if (in_array('member_profiles_masjid_id_foreign', $names)) {
                $table->dropForeign(['masjid_id']);
            }
            if (in_array('member_profiles_madarsa_id_foreign', $names)) {
                $table->dropForeign(['madarsa_id']);
            }

            $table->dropColumn(['masjid_id', 'madarsa_id']);
        });
    }
};
