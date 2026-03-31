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
        Schema::create('muqquir_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The member booking
            $table->foreignId('muqquir_profile_id')->constrained()->onDelete('cascade'); // The muqquir
            $table->date('booking_date');
            $table->string('status')->default('pending'); // pending, accepted, rejected, completed
            $table->decimal('travel_fee', 10, 2)->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muqquir_bookings');
    }
};
