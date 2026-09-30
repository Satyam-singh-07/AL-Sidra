<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'masjid_id',
        'madarsa_id',
        'donor_user_id',
        'mohalla',
        'unit_count',
        'calculated_amount',
        'paid_amount',
        'balance',
        'payment_status',
        'recorded_by_user_id',
        'notes',
    ];

    protected $casts = [
        'unit_count' => 'decimal:2',
        'calculated_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function campaign()
    {
        return $this->belongsTo(DonationCampaign::class, 'campaign_id');
    }

    public function masjid()
    {
        return $this->belongsTo(Masjid::class);
    }

    public function madarsa()
    {
        return $this->belongsTo(Madarsa::class);
    }

    public function donor()
    {
        return $this->belongsTo(User::class, 'donor_user_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    protected static function booted()
    {
        static::saving(function ($ledger) {
            $calc = (float)($ledger->calculated_amount ?? 0);
            $paid = (float)($ledger->paid_amount ?? 0);

            $ledger->balance = max(0, $calc - $paid);

            if ($calc > 0) {
                if ($paid >= $calc) {
                    $ledger->payment_status = 'paid';
                } elseif ($paid > 0) {
                    $ledger->payment_status = 'partial';
                } else {
                    $ledger->payment_status = 'unpaid';
                }
            } else {
                // By choice / voluntary
                if ($paid > 0) {
                    $ledger->payment_status = 'paid';
                } else {
                    $ledger->payment_status = 'unpaid';
                }
            }
        });
    }
}
