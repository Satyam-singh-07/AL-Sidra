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
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_category_id')->constrained();
            $table->enum('place_type', ['masjid', 'madarsa']);
            $table->unsignedBigInteger('place_id');
            $table->enum('kyc_status', [
                'not_started',
                'partial',
                'submitted',
                'approved',
                'rejected'
            ])->default('not_started');
            $table->timestamps();

            $table->index(['place_type', 'place_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_profiles');
    }
};
