<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Madarsa extends Model
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

    protected $casts = [
        'registration_date' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    protected $appends = ['passbook_url', 'video_url'];

    public function getPassbookUrlAttribute()
    {
        return $this->passbook ? url('storage/' . $this->passbook) : null;
    }

    public function getVideoUrlAttribute()
    {
        return $this->video ? url('storage/' . $this->video) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function images()
    {
        return $this->hasMany(MadarsaImage::class);
    }

    public function memberProfiles()
    {
        return $this->hasMany(MemberProfile::class, 'place_id')
            ->where('place_type', 'madarsa');
    }

    public function members()
    {
        return $this->hasManyThrough(
            User::class,
            MemberProfile::class,
            'place_id',   // MemberProfile.place_id
            'id',         // User.id
            'id',         // Madarsa.id
            'user_id'     // MemberProfile.user_id
        )->where('place_type', 'madarsa');
    }

    public function courses()
    {
        return $this->belongsToMany(MadarsaCourse::class);
    }
}
