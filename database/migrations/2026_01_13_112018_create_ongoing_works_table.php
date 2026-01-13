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
        Schema::create('ongoing_works', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('status', ['planning', 'in-progress', 'completed', 'on-hold'])->default('in-progress');
            $table->text('description');
            $table->text('address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ongoing_works');
    }
};
