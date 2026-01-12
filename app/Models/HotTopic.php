<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image',
        'video',
        'status',
        'is_trending',
        'views',
        'created_by',
    ];

    protected $appends = [
        'image_url',
        'video_url',
    ];

    public function updates()
    {
        return $this->hasMany(TopicUpdate::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return asset('images/no-image.png');
        }

        return asset('storage/' . $this->image);
    }

    public function getVideoUrlAttribute()
    {
        if (!$this->video) {
            return null;
        }

        return asset('storage/' . $this->video);
    }
}
