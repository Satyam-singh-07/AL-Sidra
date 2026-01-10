<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name','email','phone','password','status'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function memberProfile()
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function scopeMembers($query)
    {
        return $query->whereHas('roles', fn ($q) =>
            $q->where('slug', 'member')
        )->whereHas('memberProfile');
    }

    public function scopeUsers($query)
    {
        return $query->whereHas('roles', fn ($q) =>
            $q->where('slug', 'user')
        );
    }

    public function toggleStatus(): void
    {
        $this->status = $this->status === 'active' ? 'blocked' : 'active';
        $this->save();
    }

}
