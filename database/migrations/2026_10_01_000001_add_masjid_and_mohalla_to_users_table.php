<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'selected_masjid_id')) {
                $table->foreignId('selected_masjid_id')
                    ->nullable()
                    ->after('status')
                    ->constrained('masjids')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('users', 'selected_madarsa_id')) {
                $table->foreignId('selected_madarsa_id')
                    ->nullable()
                    ->after('selected_masjid_id')
                    ->constrained('madarsas')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('users', 'mohalla')) {
                $table->string('mohalla')->nullable()->after('selected_madarsa_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'selected_masjid_id')) {
                $table->dropForeign(['selected_masjid_id']);
                $table->dropColumn('selected_masjid_id');
            }

            if (Schema::hasColumn('users', 'selected_madarsa_id')) {
                $table->dropForeign(['selected_madarsa_id']);
                $table->dropColumn('selected_madarsa_id');
            }

            if (Schema::hasColumn('users', 'mohalla')) {
                $table->dropColumn('mohalla');
            }
        });
    }
};
