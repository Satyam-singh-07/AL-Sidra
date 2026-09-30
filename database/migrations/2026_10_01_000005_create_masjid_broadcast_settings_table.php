<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masjid_broadcast_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('masjid_id')->nullable()->unique()->constrained('masjids')->onDelete('cascade');
            $table->foreignId('madarsa_id')->nullable()->unique()->constrained('madarsas')->onDelete('cascade');
            $table->boolean('is_public')->default(false);
            $table->timestamp('public_until')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masjid_broadcast_settings');
    }
};
