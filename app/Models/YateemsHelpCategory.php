<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YateemsHelpCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function yateemsHelps()
    {
        return $this->hasMany(YateemsHelp::class, 'category_id');
    }
}
