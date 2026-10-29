<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrayerNotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'gregorian_date',
        'prayer',
        'masjid_id',
    ];

    public function masjid()
    {
        return $this->belongsTo(Masjid::class);
    }
}
