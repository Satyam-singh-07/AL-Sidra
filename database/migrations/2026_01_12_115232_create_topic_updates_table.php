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
        Schema::create('topic_updates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hot_topic_id')
                ->constrained('hot_topics')
                ->onDelete('cascade');

            $table->string('title', 150);
            $table->text('content');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topic_updates');
    }
};
