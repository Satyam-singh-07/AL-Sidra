<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YateemsHelpImage extends Model
{
    protected $fillable = [
        'yateems_help_id',
        'image',
    ];

    protected $appends = ['image_url'];
    
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function yateemsHelp()
    {
        return $this->belongsTo(YateemsHelp::class);
    }
}

