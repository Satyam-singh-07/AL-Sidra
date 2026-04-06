<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['unique_id', 'name', 'email', 'language', 'phone', 'password', 'status', 'profile_picture', 'address', 'latitude', 'longitude'];

    protected $appends = ['profile_picture_url'];

    public function generateUniqueId()
    {
        if ($this->roles()->where('slug', 'member')->exists()) {
            $this->unique_id = 'ASMR' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
            $this->save();
        }
    }

    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture ? Storage::disk('public')->url($this->profile_picture) : null;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function memberProfile()
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function muqquirProfile()
    {
        return $this->hasOne(MuqquirProfile::class);
    }

    public function ruhaniIjalAamil()
    {
        return $this->hasOne(RuhaniIjalAamil::class);
    }

    public function scopeMembers($query)
    {
        return $query->whereHas(
            'roles',
            fn($q) =>
            $q->where('slug', 'member')
        )->whereHas('memberProfile');
    }

    public function scopeUsers($query)
    {
        return $query->whereHas(
            'roles',
            fn($q) =>
            $q->where('slug', 'user')
        );
    }

    public function toggleStatus(): void
    {
        $this->status = $this->status === 'active' ? 'blocked' : 'active';
        $this->save();
    }

    public function masjids()
    {
        return $this->hasMany(Masjid::class);
    }

    public function yateemsHelps()
    {
        return $this->hasMany(YateemsHelp::class);
    }

    public function canAccess(string $module): bool
    {
        return $this->roles()
            ->whereHas('modules', function ($q) use ($module) {
                $q->where('module', $module);
            })
            ->exists();
    }

    public function fcmTokens()
    {
        return $this->hasMany(UserFcmToken::class);
    }
}
