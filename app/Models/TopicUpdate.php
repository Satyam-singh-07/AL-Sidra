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
    ];

    public function topic()
    {
        return $this->belongsTo(HotTopic::class, 'hot_topic_id');
    }
}

