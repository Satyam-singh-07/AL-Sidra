<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
        'menu_image',
        'video',
        'status',
        'contact_number',
        'opening_time',
        'closing_time',
    ];

    protected $appends = ['video_url', 'menu_image_url'];

    public function getVideoUrlAttribute()
    {
        return $this->video ? asset('storage/' . $this->video) : null;
    }

    public function getMenuImageUrlAttribute()
    {
        return $this->menu_image
            ? asset('storage/' . $this->menu_image)
            : null;
    }

    public function images()
    {
        return $this->hasMany(RestaurantImage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function firstImage()
    {
        return $this->hasOne(RestaurantImage::class)->oldestOfMany();
    }
}
