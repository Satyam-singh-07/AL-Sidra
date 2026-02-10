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
        Schema::create('prayer_times', function (Blueprint $table) {
            $table->id();

            $table->date('gregorian_date')->index();
            $table->string('timezone');

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            $table->json('timings');   // timings object
            $table->json('date_meta'); // hijri + gregorian details
            $table->json('meta');      // method, school, offsets, etc
            $table->json('raw');       // FULL original payload (safety net)

            $table->timestamps();

            $table->unique(['gregorian_date', 'latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prayer_times');
    }
};
