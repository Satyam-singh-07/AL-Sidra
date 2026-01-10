<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->foreignId('kyc_reviewed_by')
                ->nullable()
                ->after('kyc_status')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('kyc_reviewed_at')
                ->nullable()
                ->after('kyc_reviewed_by');

            $table->string('rejection_reason')
                ->nullable()
                ->after('kyc_reviewed_at');

            $table->text('rejection_notes')
                ->nullable()
                ->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropForeign(['kyc_reviewed_by']);
            $table->dropColumn([
                'kyc_reviewed_by',
                'kyc_reviewed_at',
                'rejection_reason',
                'rejection_notes',
            ]);
        });
    }
};

