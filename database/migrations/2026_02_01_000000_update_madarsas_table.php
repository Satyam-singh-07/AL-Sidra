<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('madarsas', function (Blueprint $table) {
            if (!Schema::hasColumn('madarsas', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('madarsas', 'gender')) {
                $table->enum('gender', ['male', 'female'])->after('name');
            }

            if (!Schema::hasColumn('madarsas', 'address')) {
                $table->text('address')->after('gender');
            }

            if (!Schema::hasColumn('madarsas', 'latitude')) {
                $table->decimal('latitude', 10, 7)->after('address');
            }

            if (!Schema::hasColumn('madarsas', 'longitude')) {
                $table->decimal('longitude', 10, 7)->after('latitude');
            }

            if (!Schema::hasColumn('madarsas', 'community_id')) {
                $table->foreignId('community_id')
                    ->nullable()
                    ->after('address')
                    ->constrained()
                    ->restrictOnDelete();
            }

            if (!Schema::hasColumn('madarsas', 'status')) {
                $table->enum('status', ['active', 'pending', 'inactive'])
                    ->default('active')
                    ->after('community_id');
            }

            if (!Schema::hasColumn('madarsas', 'passbook')) {
                $table->string('passbook')->nullable()->after('status');
            }

            if (!Schema::hasColumn('madarsas', 'registration_number')) {
                $table->string('registration_number')->nullable()->after('passbook');
            }

            if (!Schema::hasColumn('madarsas', 'registration_date')) {
                $table->date('registration_date')->nullable()->after('registration_number');
            }

            if (!Schema::hasColumn('madarsas', 'video')) {
                $table->string('video')->nullable()->after('registration_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('madarsas', function (Blueprint $table) {
            $foreignKeyExists = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'madarsas'
                AND COLUMN_NAME = 'user_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            if ($foreignKeyExists) {
                $table->dropForeign(['user_id']);
            }

            if (Schema::hasColumn('madarsas', 'user_id')) {
                $table->dropColumn('user_id');
            }

            $communityFkExists = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'madarsas'
                AND COLUMN_NAME = 'community_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            if ($communityFkExists) {
                $table->dropForeign(['community_id']);
            }

            if (Schema::hasColumn('madarsas', 'community_id')) {
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
                if (Schema::hasColumn('madarsas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
