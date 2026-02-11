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
        Schema::create('prayer_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->date('gregorian_date');
            $table->string('prayer'); 
            $table->timestamps();

            $table->unique(['gregorian_date', 'prayer']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prayer_notification_logs');
    }
};
