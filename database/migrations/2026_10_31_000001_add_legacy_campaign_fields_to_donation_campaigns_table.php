<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donation_campaigns', function (Blueprint $table) {
            $table->boolean('is_legacy')->default(false)->after('status');
            $table->decimal('prior_collected_amount', 12, 2)->nullable()->after('is_legacy');
            $table->text('legacy_notes')->nullable()->after('prior_collected_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donation_campaigns', function (Blueprint $table) {
            $table->dropColumn(['is_legacy', 'prior_collected_amount', 'legacy_notes']);
        });
    }
};

