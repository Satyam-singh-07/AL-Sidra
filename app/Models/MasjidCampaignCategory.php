<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasjidCampaignCategory extends Model
{
    use HasFactory;

    protected $table = 'masjid_campaign_categories';

    protected $fillable = [
        'masjid_id',
        'name',
        'unit_label',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'masjid_id');
    }
}

