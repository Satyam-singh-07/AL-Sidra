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

    public function yateemsHelp()
    {
        return $this->belongsTo(YateemsHelp::class);
    }
}

