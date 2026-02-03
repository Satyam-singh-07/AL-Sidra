<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MadarsaImage extends Model
{
    use HasFactory;

    protected $fillable = ['madarsa_id', 'image_path'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

    public function madarsa()
    {
        return $this->belongsTo(Madarsa::class);
    }
}
