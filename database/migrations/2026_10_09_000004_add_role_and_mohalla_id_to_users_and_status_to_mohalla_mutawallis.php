<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'mohalla_id')) {
                $table->foreignId('mohalla_id')->nullable()->after('mohalla')->constrained('mohallas')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('mohalla_id');
            }
        });

        Schema::table('mohalla_mutawallis', function (Blueprint $table) {
            if (!Schema::hasColumn('mohalla_mutawallis', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('assigned_mohalla');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'mohalla_id')) {
                $table->dropForeign(['mohalla_id']);
                $table->dropColumn('mohalla_id');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });

        Schema::table('mohalla_mutawallis', function (Blueprint $table) {
            if (Schema::hasColumn('mohalla_mutawallis', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
