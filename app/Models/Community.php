<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function masjid()
    {
        return $this->hasOne(Masjid::class);
    }

    public function madarsa()
    {
        return $this->hasOne(Madarsa::class);
    }
}

