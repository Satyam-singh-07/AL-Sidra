<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyQuote extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote',
        'scheduled_time',
        'is_active',
        'last_sent_at',
    ];

    public function logs()
    {
        return $this->hasMany(DailyQuoteLog::class);
    }
}
