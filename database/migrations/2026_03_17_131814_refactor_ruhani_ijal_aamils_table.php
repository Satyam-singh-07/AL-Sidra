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
        // Remove ruhani_ijal_category_id from ruhani_ijal_aamils
        Schema::table('ruhani_ijal_aamils', function (Blueprint $table) {
            $table->dropForeign(['ruhani_ijal_category_id']);
            $table->dropColumn('ruhani_ijal_category_id');
        });

        // Create pivot table for ruhani_ijal_aamil_category
        Schema::create('ruhani_ijal_aamil_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruhani_ijal_aamil_id')->constrained()->onDelete('cascade');
            $table->foreignId('ruhani_ijal_category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ruhani_ijal_aamils', function (Blueprint $table) {
            $table->foreignId('ruhani_ijal_category_id')->nullable()->constrained()->onDelete('cascade');
        });

        Schema::dropIfExists('ruhani_ijal_aamil_category');
    }
};
