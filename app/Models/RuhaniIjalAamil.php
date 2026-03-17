<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuhaniIjalAamil extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ruhani_ijal_category_id',
        'experience',
        'description',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(RuhaniIjalCategory::class, 'ruhani_ijal_category_id');
    }
}
