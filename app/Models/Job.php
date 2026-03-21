<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_category_id',
        'place_type',
        'place_id',
        'title',
        'description',
        'requirements',
        'salary_range',
        'contact_person',
        'contact_phone',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }

    public function place()
    {
        return $this->place_type === 'masjid'
            ? $this->belongsTo(Masjid::class, 'place_id')
            : $this->belongsTo(Madarsa::class, 'place_id');
    }
}
