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
        Schema::create('yateems_helps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
                
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['pending', 'active', 'inactive'])->default('pending');
            $table->string('bank_name');
            $table->string('ifsc_code', 20);
            $table->string('account_no', 50);
            $table->string('video')->nullable();
            $table->string('upi_id')->nullable();

            $table->string('qr_code')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yateems_helps');
    }
};
