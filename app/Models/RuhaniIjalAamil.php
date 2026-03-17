<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuhaniIjalAamil extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'experience',
        'description',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The categories that belong to the Aamil.
     */
    public function categories()
    {
        return $this->belongsToMany(RuhaniIjalCategory::class, 'ruhani_ijal_aamil_category', 'ruhani_ijal_aamil_id', 'ruhani_ijal_category_id');
    }
}
