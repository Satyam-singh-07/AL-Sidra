<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = ['image','status'];

    protected $casts = [
        'status' => 'string',
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value
                ? asset('storage/' . $value)
                : asset('images/no-image.png'),
        );
    }
}
