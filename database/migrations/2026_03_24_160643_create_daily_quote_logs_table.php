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
        Schema::create('daily_quote_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_quote_id')->constrained()->onDelete('cascade');
            $table->integer('sent_to_count')->default(0);
            $table->timestamp('sent_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_quote_logs');
    }
};
