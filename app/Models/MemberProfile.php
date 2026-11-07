<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberProfile extends Model
{
    protected $fillable = [
        'user_id',
        'member_category_id',
        'masjid_id',
        'madarsa_id',
        'place_type',
        'place_id',
        'kyc_status',
        'kyc_reviewed_by',
        'kyc_reviewed_at',
        'rejection_reason',
        'rejection_notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(MemberCategory::class, 'member_category_id');
    }

    public function masjid()
    {
        return $this->belongsTo(Masjid::class);
    }

    public function madarsa()
    {
        return $this->belongsTo(Madarsa::class);
    }

    public function kyc()
    {
        return $this->hasOne(MemberKycDocument::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'kyc_reviewed_by');
    }
    
    // Native Polymorphic Relation
    public function place()
    {
        return $this->morphTo(__FUNCTION__, 'place_type', 'place_id');
    }
}

