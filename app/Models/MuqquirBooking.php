<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MuqquirBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'muqquir_profile_id',
        'booking_date',
        'status',
        'travel_fee',
        'details',
    ];

    protected $casts = [
        'booking_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function muqquirProfile()
    {
        return $this->belongsTo(MuqquirProfile::class);
    }
}
