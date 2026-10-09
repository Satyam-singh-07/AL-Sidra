<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MohallaMutawalli extends Model
{
    use HasFactory;

    protected $fillable = [
        'masjid_id',
        'madarsa_id',
        'user_id',
        'mohalla_id',
        'assigned_mohalla',
        'status',
    ];

    public function mohallaRecord(): BelongsTo
    {
        return $this->belongsTo(Mohalla::class, 'mohalla_id');
    }

    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class);
    }

    public function madarsa(): BelongsTo
    {
        return $this->belongsTo(Madarsa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
