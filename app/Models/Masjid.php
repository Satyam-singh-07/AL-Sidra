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

    protected $appends = ['video_url','passbook_url'];

    public function getVideoUrlAttribute()
    {
        return $this->video ? asset('storage/' . $this->video) : null;
    }

    public function getPassbookUrlAttribute()
    {
        return $this->passbook ? asset('storage/' . $this->passbook) : null;
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

    public function memberProfiles()
    {
        return $this->hasMany(MemberProfile::class, 'place_id')
            ->where('place_type', 'masjid');
    }

    public function members()
    {
        return $this->hasManyThrough(
            User::class,
            MemberProfile::class,
            'place_id',   // Foreign key on MemberProfile table
            'id',         // Foreign key on User table
            'id',         // Local key on Masjid table
            'user_id'     // Local key on MemberProfile table
        )->where('place_type', 'masjid');
    }
}
