<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MasjidBroadcastSetting extends Model
{
    protected $fillable = [
        'masjid_id',
        'madarsa_id',
        'is_public',
        'public_until',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'public_until' => 'datetime',
    ];

    public function masjid()
    {
        return $this->belongsTo(Masjid::class);
    }

    public function madarsa()
    {
        return $this->belongsTo(Madarsa::class);
    }

    public function isCurrentlyPublic(): bool
    {
        return $this->is_public && $this->public_until && Carbon::now()->isBefore($this->public_until);
    }

    public function getDaysRemainingAttribute(): int
    {
        if (!$this->isCurrentlyPublic() || !$this->public_until) {
            return 0;
        }

        return max(0, Carbon::now()->diffInDays($this->public_until, false) + 1);
    }
}
