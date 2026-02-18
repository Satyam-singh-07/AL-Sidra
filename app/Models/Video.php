<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'title',
        'video_path',
        'status',
        'video_category_id'
    ];

    public function category()
    {
        return $this->belongsTo(VideoCategory::class, 'video_category_id');
    }
}
