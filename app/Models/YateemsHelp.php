<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YateemsHelp extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'bank_name',
        'ifsc_code',
        'account_no',
        'upi_id',
        'qr_code',
        'status',
        'video',
    ];

    protected $appends = ['video_url','qr_code_url'];

    public function getVideoUrlAttribute()
    {
        return $this->video ? asset('storage/' . $this->video) : null;
    }
    
    public function getQrCodeUrlAttribute()
    {
        return $this->qr_code ? asset('storage/' . $this->qr_code) : null;
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function images()
    {
        return $this->hasMany(YateemsHelpImage::class);
    }

    public function document()
    {
        return $this->hasOne(YateemsHelpDocument::class);
    }
}
