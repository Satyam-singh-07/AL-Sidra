<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerTime extends Model
{
    protected $fillable = [
        'gregorian_date',
        'timezone',
        'latitude',
        'longitude',
        'timings',
        'date_meta',
        'meta',
        'raw',
    ];

    protected $casts = [
        'timings'   => 'array',
        'date_meta' => 'array',
        'meta'      => 'array',
        'raw'       => 'array',
    ];
}
