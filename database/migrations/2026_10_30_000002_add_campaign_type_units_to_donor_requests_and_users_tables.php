<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add campaign type unit counts to donor_requests table
        Schema::table('donor_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('donor_requests', 'family_count')) {
                $table->unsignedInteger('family_count')->default(0)->after('father_name');
            }
            if (!Schema::hasColumn('donor_requests', 'nikkah_count')) {
                $table->unsignedInteger('nikkah_count')->default(0)->after('family_count');
            }
            if (!Schema::hasColumn('donor_requests', 'land_count')) {
                $table->decimal('land_count', 12, 2)->default(0)->after('nikkah_count');
            }
        });

        // 2. Add campaign type unit counts to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'family_count')) {
                $table->unsignedInteger('family_count')->default(0)->after('father_name');
            }
            if (!Schema::hasColumn('users', 'nikkah_count')) {
                $table->unsignedInteger('nikkah_count')->default(0)->after('family_count');
            }
            if (!Schema::hasColumn('users', 'land_count')) {
                $table->decimal('land_count', 12, 2)->default(0)->after('nikkah_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('donor_requests', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('donor_requests', 'family_count')) $cols[] = 'family_count';
            if (Schema::hasColumn('donor_requests', 'nikkah_count')) $cols[] = 'nikkah_count';
            if (Schema::hasColumn('donor_requests', 'land_count')) $cols[] = 'land_count';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('users', 'family_count')) $cols[] = 'family_count';
            if (Schema::hasColumn('users', 'nikkah_count')) $cols[] = 'nikkah_count';
            if (Schema::hasColumn('users', 'land_count')) $cols[] = 'land_count';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};

