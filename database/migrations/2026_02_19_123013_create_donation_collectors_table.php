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
        Schema::create('donation_collectors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('madarsa_id')
                ->constrained('madarsas')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('contact', 20);
            $table->text('address')->nullable();

            $table->timestamps();

            $table->index('madarsa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_collectors');
    }
};
