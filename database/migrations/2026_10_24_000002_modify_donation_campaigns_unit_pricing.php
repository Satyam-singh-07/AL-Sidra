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
            // 1. Add unit_price column (aliased with rate_per_unit)
            if (!Schema::hasColumn('donation_campaigns', 'unit_price')) {
                $table->decimal('unit_price', 12, 2)->nullable()->after('rate_per_unit');
            }

            // 2. Make target_mohalla nullable if it exists, default null (applies to all mohallas)
            if (Schema::hasColumn('donation_campaigns', 'target_mohalla')) {
                $table->string('target_mohalla')->nullable()->default(null)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donation_campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('donation_campaigns', 'unit_price')) {
                $table->dropColumn('unit_price');
            }
        });
    }
};
