<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'father_name')) {
                $table->string('father_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'unit_count')) {
                $table->unsignedInteger('unit_count')->default(1)->after('father_name');
            }
            if (!Schema::hasColumn('users', 'notes')) {
                $table->text('notes')->nullable()->after('mohalla');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('users', 'father_name')) {
                $columnsToDrop[] = 'father_name';
            }
            if (Schema::hasColumn('users', 'unit_count')) {
                $columnsToDrop[] = 'unit_count';
            }
            if (Schema::hasColumn('users', 'notes')) {
                $columnsToDrop[] = 'notes';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
