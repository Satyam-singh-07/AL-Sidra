<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create masjid_campaign_categories table
        if (!Schema::hasTable('masjid_campaign_categories')) {
            Schema::create('masjid_campaign_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('masjid_id')->constrained('masjids')->cascadeOnDelete();
                $table->string('name');
                $table->string('unit_label')->default('Unit');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Add category_units JSON column to donor_requests and users
        Schema::table('donor_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('donor_requests', 'category_units')) {
                $table->json('category_units')->nullable()->after('land_count');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'category_units')) {
                $table->json('category_units')->nullable()->after('land_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'category_units')) {
                $table->dropColumn('category_units');
            }
        });

        Schema::table('donor_requests', function (Blueprint $table) {
            if (Schema::hasColumn('donor_requests', 'category_units')) {
                $table->dropColumn('category_units');
            }
        });

        Schema::dropIfExists('masjid_campaign_categories');
    }
};

