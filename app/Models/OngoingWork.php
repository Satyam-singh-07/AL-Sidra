<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OngoingWork extends Model
{
    protected $fillable = [
        'title',
        'status',
        'description',
        'address',
    ];

    public function images()
    {
        return $this->hasMany(OngoingWorkImage::class);
    }

    public function videos()
    {
        return $this->hasMany(OngoingWorkVideo::class);
    }
}

