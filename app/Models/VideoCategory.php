<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'image'
    ];

    public function videos()
    {
        return $this->hasMany(Video::class, 'video_category_id');
    }
}
