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

    protected $appends = ['aadhaar_front_url', 'aadhaar_back_url'];

    public function getAadhaarFrontUrlAttribute()
    {
        return $this->aadhaar_front ? asset('storage/' . $this->aadhaar_front) : null;
    }
    
    public function getAadhaarBackUrlAttribute()
    {
        return $this->aadhaar_back ? asset('storage/' . $this->aadhaar_back) : null;
    }

    public function yateemsHelp()
    {
        return $this->belongsTo(YateemsHelp::class);
    }
}

