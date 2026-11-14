<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdhanAudio extends Model
{
    use HasFactory;

    protected $table = 'adhan_audios';

    protected $fillable = [
        'title',
        'reciter',
        'audio_path',
        'duration',
        'status',
    ];

    protected $appends = ['audio_url'];

    public function getAudioUrlAttribute(): ?string
    {
        return $this->audio_path ? asset('storage/' . $this->audio_path) : null;
    }

    public function masjids()
    {
        return $this->hasMany(Masjid::class, 'adhan_audio_id');
    }
}
