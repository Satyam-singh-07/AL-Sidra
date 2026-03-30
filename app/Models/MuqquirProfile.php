<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MuqquirProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'account_no',
        'ifsc_code',
        'travel_fee',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function videos()
    {
        return $this->hasMany(MuqquirVideo::class);
    }

    public function availabilities()
    {
        return $this->hasMany(MuqquirAvailability::class);
    }
}
