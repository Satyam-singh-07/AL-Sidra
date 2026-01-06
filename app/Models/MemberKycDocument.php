<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberKycDocument extends Model
{
    protected $fillable = [
        'member_profile_id',
        'institute_name',
        'degree_complete_year',
        'degree_photo_path',
        'aadhaar_front_path',
        'aadhaar_back_path',
        'submitted_at'
    ];

    public function memberProfile()
    {
        return $this->belongsTo(MemberProfile::class);
    }
}

