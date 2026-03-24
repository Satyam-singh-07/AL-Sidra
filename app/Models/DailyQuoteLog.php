<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyQuoteLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_quote_id',
        'sent_to_count',
        'sent_at',
    ];

    public function quote()
    {
        return $this->belongsTo(DailyQuote::class, 'daily_quote_id');
    }
}
