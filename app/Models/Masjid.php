<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Masjid extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'gender',
        'address',
        'community_id',
        'status',
        'passbook',
        'registration_number',
        'registration_date',
        'video',
        'latitude',
        'longitude',
    ];

    protected $appends = ['video_url'];

    public function getVideoUrlAttribute()
    {
        return $this->video ? asset('storage/' . $this->video) : null;
    }

    public function images()
    {
        return $this->hasMany(MasjidImage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }
}
