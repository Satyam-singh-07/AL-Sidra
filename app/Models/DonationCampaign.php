<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'masjid_id',
        'madarsa_id',
        'created_by',
        'name',
        'category',
        'rate_per_unit',
        'target_amount',
        'description',
        'status',
    ];

    protected $casts = [
        'rate_per_unit' => 'decimal:2',
        'target_amount' => 'decimal:2',
    ];

    public function masjid()
    {
        return $this->belongsTo(Masjid::class);
    }

    public function madarsa()
    {
        return $this->belongsTo(Madarsa::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ledgers()
    {
        return $this->hasMany(DonationLedger::class, 'campaign_id');
    }
}
