<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MuqquirVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'muqquir_profile_id',
        'video_path',
    ];

    public function muqquirProfile()
    {
        return $this->belongsTo(MuqquirProfile::class);
    }
}
