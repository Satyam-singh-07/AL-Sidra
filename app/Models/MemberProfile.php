<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberProfile extends Model
{
    protected $fillable = [
        'user_id',
        'member_category_id',
        'place_type',
        'place_id',
        'kyc_status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(MemberCategory::class, 'member_category_id');
    }

    public function kyc()
    {
        return $this->hasOne(MemberKycDocument::class);
    }

    // Polymorphic-style resolver
    public function place()
    {
        return $this->place_type === 'masjid'
            ? $this->belongsTo(Masjid::class, 'place_id')
            : $this->belongsTo(Madarsa::class, 'place_id');
    }
}

