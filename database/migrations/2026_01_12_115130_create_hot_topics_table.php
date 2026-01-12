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
        Schema::create('hot_topics', function (Blueprint $table) {
            $table->id();

            $table->string('title', 150);
            $table->text('description');

            $table->string('image')->nullable();
            $table->string('video')->nullable();

            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->boolean('is_trending')->default(false);

            $table->unsignedBigInteger('views')->default(0);

            $table->unsignedBigInteger('created_by')->nullable(); // admin id later

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hot_topics');
    }
};
