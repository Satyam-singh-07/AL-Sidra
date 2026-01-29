<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('masjids', function (Blueprint $table) {

            if (!Schema::hasColumn('masjids', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('masjids', 'gender')) {
                $table->enum('gender', ['male', 'female'])->after('name');
            }

            if (!Schema::hasColumn('masjids', 'address')) {
                $table->text('address')->after('gender');
            }

            if (!Schema::hasColumn('masjids', 'latitude')) {
                $table->decimal('latitude', 10, 7)->after('address');
            }

            if (!Schema::hasColumn('masjids', 'longitude')) {
                $table->decimal('longitude', 10, 7)->after('latitude');
            }

            if (!Schema::hasColumn('masjids', 'community_id')) {
                $table->foreignId('community_id')
                    ->nullable()
                    ->after('address')
                    ->constrained()
                    ->restrictOnDelete();
            }



            if (!Schema::hasColumn('masjids', 'status')) {
                $table->enum('status', ['active', 'pending', 'inactive'])
                    ->default('active')
                    ->after('community_id');
            }

            if (!Schema::hasColumn('masjids', 'passbook')) {
                $table->string('passbook')->nullable()->after('status');
            }

            if (!Schema::hasColumn('masjids', 'registration_number')) {
                $table->string('registration_number')->nullable()->after('passbook');
            }

            if (!Schema::hasColumn('masjids', 'registration_date')) {
                $table->date('registration_date')->nullable()->after('registration_number');
            }

            if (!Schema::hasColumn('masjids', 'video')) {
                $table->string('video')->nullable()->after('registration_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('masjids', function (Blueprint $table) {

            // Check if foreign key exists before dropping
            $foreignKeyExists = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'masjids'
                AND COLUMN_NAME = 'user_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            if ($foreignKeyExists) {
                $table->dropForeign(['user_id']);
            }

            if (Schema::hasColumn('masjids', 'user_id')) {
                $table->dropColumn('user_id');
            }

            $communityFkExists = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'masjids'
                AND COLUMN_NAME = 'community_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            if ($communityFkExists) {
                $table->dropForeign(['community_id']);
            }

            if (Schema::hasColumn('masjids', 'community_id')) {
                $table->dropColumn('community_id');
            }

            $columns = [
                'gender',
                'address',
                'community_id',
                'latitude',
                'longitude',
                'status',
                'passbook',
                'registration_number',
                'registration_date',
                'video',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('masjids', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
