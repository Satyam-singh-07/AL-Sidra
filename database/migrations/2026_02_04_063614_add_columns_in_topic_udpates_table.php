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
        Schema::table('topic_updates', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('content');
            $table->string('video_path')->nullable()->after('image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('topic_updates', function (Blueprint $table) {
            $table->dropColumn('image_path');
            $table->dropColumn('video_path');
        });
    }
};
