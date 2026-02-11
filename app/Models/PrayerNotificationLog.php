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
    ];
}
