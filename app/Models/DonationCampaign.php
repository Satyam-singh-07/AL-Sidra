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
        'target_mohalla',
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

    public function place()
    {
        return $this->masjid_id ? $this->masjid() : $this->madarsa();
    }

    public function getPlaceAttribute()
    {
        return $this->masjid ?? $this->madarsa;
    }

    public function getTitleAttribute()
    {
        return $this->name;
    }

    public function getPlaceIdAttribute()
    {
        return $this->masjid_id ?? $this->madarsa_id;
    }

    public function expenses()
    {
        return $this->hasMany(MasjidExpense::class, 'campaign_id')->where('entry_type', 'expense');
    }

    public function getTotalExpensesAttribute()
    {
        return (float) $this->expenses()->sum('amount');
    }

    public function getNetBalanceAttribute()
    {
        $totalRaised = (float) $this->ledgers()->sum('paid_amount');
        return $totalRaised - $this->total_expenses;
    }
}
