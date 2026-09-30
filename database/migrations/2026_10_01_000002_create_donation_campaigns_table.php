<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('masjid_id')->nullable()->constrained('masjids')->onDelete('cascade');
            $table->foreignId('madarsa_id')->nullable()->constrained('madarsas')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->enum('category', ['zameen', 'mard', 'nikah', 'by_choice']);
            $table->decimal('rate_per_unit', 10, 2)->nullable();
            $table->decimal('target_amount', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'completed', 'archived'])->default('active');
            $table->timestamps();

            $table->index(['masjid_id', 'status']);
            $table->index(['madarsa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_campaigns');
    }
};
