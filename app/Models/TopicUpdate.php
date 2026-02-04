<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopicUpdate extends Model
{
    protected $fillable = [
        'hot_topic_id',
        'title',
        'content',
        'image_path',
        'video_path',
    ];

    protected $appends = ['image_url', 'video_url'];

    public function getImageUrlAttribute()
    {
        return $this->image_path
            ? asset('storage/' . $this->image_path)
            : null;
    }

    public function getVideoUrlAttribute()
    {
        return $this->video_path
            ? asset('storage/' . $this->video_path)
            : null;
    }

    public function topic()
    {
        return $this->belongsTo(HotTopic::class, 'hot_topic_id');
    }
}
