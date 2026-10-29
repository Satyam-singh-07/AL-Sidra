<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('masjid_prayer_timings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('masjid_id')->constrained('masjids')->cascadeOnDelete();
            $table->date('date');
            $table->string('fajr', 5);
            $table->string('sunrise', 5)->nullable();
            $table->string('dhuhr', 5);
            $table->string('asr', 5);
            $table->string('maghrib', 5);
            $table->string('isha', 5);
            $table->string('jummah', 5)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['masjid_id', 'date'], 'unique_masjid_date');
            $table->index(['masjid_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('masjid_prayer_timings');
    }
};

