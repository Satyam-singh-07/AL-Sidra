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

    protected $fillable = [
        'unique_id',
        'name',
        'email',
        'language',
        'phone',
        'password',
        'status',
        'profile_picture',
        'address',
        'latitude',
        'longitude',
        'selected_masjid_id',
        'selected_madarsa_id',
        'mohalla',
        'mohalla_id',
        'role',
    ];

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
        return $this->profile_picture ? asset('storage/' . $this->profile_picture) : null;
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
        if ($this->isSuperAdmin()) {
            return true;
        }

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

    public function isSuperAdmin(): bool
    {
        return $this->roles()->where('slug', 'super_admin')->exists();
    }

    public function selectedMasjid()
    {
        return $this->belongsTo(Masjid::class, 'selected_masjid_id');
    }

    public function selectedMadarsa()
    {
        return $this->belongsTo(Madarsa::class, 'selected_madarsa_id');
    }

    public function donationLedgers()
    {
        return $this->hasMany(DonationLedger::class, 'donor_user_id');
    }

    public function mohallaRecord()
    {
        return $this->belongsTo(Mohalla::class, 'mohalla_id');
    }
}

