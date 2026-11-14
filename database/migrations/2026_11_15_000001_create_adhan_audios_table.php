<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('adhan_audios')) {
            Schema::create('adhan_audios', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('reciter')->nullable();
                $table->string('audio_path');
                $table->string('duration')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('masjids', 'adhan_audio_id')) {
            Schema::table('masjids', function (Blueprint $table) {
                $table->unsignedBigInteger('adhan_audio_id')->nullable()->after('adhan_audio');
                $table->foreign('adhan_audio_id')->references('id')->on('adhan_audios')->nullOnDelete();
            });
        }

        // Migrate any existing masjids with adhan_audio into adhan_audios table
        try {
            $existingMasjids = DB::table('masjids')->whereNotNull('adhan_audio')->get();
            foreach ($existingMasjids as $masjid) {
                if (!empty($masjid->adhan_audio)) {
                    $newId = DB::table('adhan_audios')->insertGetId([
                        'title'      => ($masjid->name ?? 'Masjid') . ' Adhan',
                        'reciter'    => 'Masjid Muazzin',
                        'audio_path' => $masjid->adhan_audio,
                        'status'     => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    DB::table('masjids')->where('id', $masjid->id)->update([
                        'adhan_audio_id' => $newId,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore during standalone execution if table doesn't have records
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('masjids', 'adhan_audio_id')) {
            Schema::table('masjids', function (Blueprint $table) {
                $table->dropForeign(['adhan_audio_id']);
                $table->dropColumn('adhan_audio_id');
            });
        }

        Schema::dropIfExists('adhan_audios');
    }
};
