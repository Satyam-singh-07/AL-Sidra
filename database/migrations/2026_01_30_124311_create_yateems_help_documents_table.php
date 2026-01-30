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
        Schema::create('yateems_help_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('yateems_help_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('aadhaar_front')->nullable();
            $table->string('aadhaar_back')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yateems_help_documents');
    }
};
