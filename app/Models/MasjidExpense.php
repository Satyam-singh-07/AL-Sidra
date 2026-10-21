<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasjidExpense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'masjid_id',
        'madarsa_id',
        'campaign_id',
        'recorded_by_user_id',
        'entry_type',
        'title',
        'amount',
        'category',
        'payment_mode',
        'transaction_date',
        'vendor_name',
        'receipt_url',
        'notes',
        'status',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'transaction_date' => 'date:Y-m-d',
    ];

    protected $appends = [
        'receipt_full_url',
    ];

    public function getReceiptFullUrlAttribute()
    {
        if (!$this->receipt_url) {
            return null;
        }

        if (str_starts_with($this->receipt_url, 'http://') || str_starts_with($this->receipt_url, 'https://')) {
            return $this->receipt_url;
        }

        return asset('storage/' . $this->receipt_url);
    }

    // Relationships
    public function masjid()
    {
        return $this->belongsTo(Masjid::class);
    }

    public function madarsa()
    {
        return $this->belongsTo(Madarsa::class);
    }

    public function campaign()
    {
        return $this->belongsTo(DonationCampaign::class, 'campaign_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    // Scopes
    public function scopeExpenses($query)
    {
        return $query->where('entry_type', 'expense');
    }

    public function scopeIncomes($query)
    {
        return $query->where('entry_type', 'income');
    }

    public function scopeForPlace($query, $masjidId = null, $madarsaId = null)
    {
        return $query->when($masjidId, fn($q) => $q->where('masjid_id', $masjidId))
                     ->when($madarsaId, fn($q) => $q->where('madarsa_id', $madarsaId));
    }
}
