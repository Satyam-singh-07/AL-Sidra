<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MadarsaCourse extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function madarsas()
    {
        return $this->belongsToMany(
            Madarsa::class,
            'madarsa_course',
            'madarsa_course_id',
            'madarsa_id'
        )->withTimestamps();
    }
}
