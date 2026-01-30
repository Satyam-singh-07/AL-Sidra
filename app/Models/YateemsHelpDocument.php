<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YateemsHelpDocument extends Model
{
    protected $fillable = [
        'yateems_help_id',
        'aadhaar_front',
        'aadhaar_back',
    ];

    public function yateemsHelp()
    {
        return $this->belongsTo(YateemsHelp::class);
    }
}

