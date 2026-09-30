<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MohallaMutawalli extends Model
{
    use HasFactory;

    protected $fillable = [
        'masjid_id',
        'madarsa_id',
        'user_id',
        'assigned_mohalla',
    ];

    public function masjid()
    {
        return $this->belongsTo(Masjid::class);
    }

    public function madarsa()
    {
        return $this->belongsTo(Madarsa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
