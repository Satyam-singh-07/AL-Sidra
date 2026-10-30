<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DonorRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'masjid_id',
        'madarsa_id',
        'applicant_name',
        'father_name',
        'unit_count',
        'family_count',
        'nikkah_count',
        'land_count',
        'phone',
        'mohalla_id',
        'mohalla',
        'status',
        'reviewed_by_user_id',
        'reviewed_at',
        'notes',
        'rejection_reason',
    ];

    protected $casts = [
        'unit_count'   => 'integer',
        'family_count' => 'integer',
        'nikkah_count' => 'integer',
        'land_count'   => 'decimal:2',
        'reviewed_at'  => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'masjid_id');
    }

    public function madarsa(): BelongsTo
    {
        return $this->belongsTo(Madarsa::class, 'madarsa_id');
    }

    public function mohallaRelation(): BelongsTo
    {
        return $this->belongsTo(Mohalla::class, 'mohalla_id');
    }

    public function mohallaRecord(): BelongsTo
    {
        return $this->belongsTo(Mohalla::class, 'mohalla_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForMasjid($query, $masjidId)
    {
        return $query->where('masjid_id', $masjidId);
    }

    public function scopeForMadarsa($query, $madarsaId)
    {
        return $query->where('madarsa_id', $madarsaId);
    }
}
