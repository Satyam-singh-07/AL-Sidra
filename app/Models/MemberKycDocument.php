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

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    protected $appends = [
        'degree_photo_url',
        'aadhaar_front_url',
        'aadhaar_back_url',
    ];

    public function memberProfile()
    {
        return $this->belongsTo(MemberProfile::class);
    }

    /* ================= ACCESSORS ================= */

    public function getDegreePhotoUrlAttribute()
    {
        return $this->degree_photo_path
            ? asset('storage/' . $this->degree_photo_path)
            : null;
    }

    public function getAadhaarFrontUrlAttribute()
    {
        return $this->aadhaar_front_path
            ? asset('storage/' . $this->aadhaar_front_path)
            : null;
    }

    public function getAadhaarBackUrlAttribute()
    {
        return $this->aadhaar_back_path
            ? asset('storage/' . $this->aadhaar_back_path)
            : null;
    }
}


