<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('donation_campaigns')->onDelete('cascade');
            $table->foreignId('masjid_id')->nullable()->constrained('masjids')->onDelete('cascade');
            $table->foreignId('madarsa_id')->nullable()->constrained('madarsas')->onDelete('cascade');
            $table->foreignId('donor_user_id')->constrained('users')->onDelete('cascade');
            $table->string('mohalla')->nullable();
            $table->decimal('unit_count', 8, 2)->nullable()->default(1.00); // e.g. 3 heads or 100 sqft
            $table->decimal('calculated_amount', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('unpaid');
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['masjid_id', 'campaign_id', 'donor_user_id']);
            $table->index(['masjid_id', 'mohalla']);
            $table->index(['madarsa_id', 'campaign_id', 'donor_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_ledgers');
    }
};
