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
        Schema::create('madarsa_course', function (Blueprint $table) {
            $table->id();

            $table->foreignId('madarsa_id')
                ->constrained('madarsas')
                ->cascadeOnDelete();

            $table->foreignId('madarsa_course_id')
                ->constrained('madarsa_courses')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['madarsa_id', 'madarsa_course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('madarsa_course');
    }
};
