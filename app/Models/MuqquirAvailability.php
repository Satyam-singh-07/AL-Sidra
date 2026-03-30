<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MuqquirAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'muqquir_profile_id',
        'available_date',
        'status',
    ];

    protected $casts = [
        'available_date' => 'date',
    ];

    public function muqquirProfile()
    {
        return $this->belongsTo(MuqquirProfile::class);
    }
}
