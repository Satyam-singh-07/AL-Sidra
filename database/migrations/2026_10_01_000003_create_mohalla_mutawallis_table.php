<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mohalla_mutawallis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('masjid_id')->nullable()->constrained('masjids')->onDelete('cascade');
            $table->foreignId('madarsa_id')->nullable()->constrained('madarsas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('assigned_mohalla');
            $table->timestamps();

            $table->index(['masjid_id', 'user_id']);
            $table->index(['madarsa_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mohalla_mutawallis');
    }
};
