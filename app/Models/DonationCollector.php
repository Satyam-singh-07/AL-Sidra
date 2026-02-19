<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonationCollector extends Model
{
    protected $fillable = [
        'madarsa_id',
        'name',
        'contact',
        'address',
    ];

    public function madarsa()
    {
        return $this->belongsTo(Madarsa::class);
    }
}
