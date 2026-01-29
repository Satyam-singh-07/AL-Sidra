<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasjidImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'masjid_id',
        'image_path',
    ];

    public function masjid()
    {
        return $this->belongsTo(Masjid::class);
    }
}
