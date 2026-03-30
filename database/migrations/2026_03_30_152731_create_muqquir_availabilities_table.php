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
        Schema::create('muqquir_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muqquir_profile_id')->constrained('muqquir_profiles')->onDelete('cascade');
            $table->date('available_date');
            $table->enum('status', ['available', 'booked'])->default('available');
            $table->timestamps();

            $table->unique(['muqquir_profile_id', 'available_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muqquir_availabilities');
    }
};
