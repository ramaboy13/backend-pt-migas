<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'user' => [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'roles' => $this->getRoleNames(),
                'is_active' => $this->is_active,
            ]
        ];
    }

    public function getKeyType()
    {
        return 'string';
    }
    public function getRoleNames(): array
    {
        return $this->roles->pluck('name')->toArray();
    }
        public function emailVerifications()
    {
        return $this->hasMany(EmailVerification::class);
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function createdReferralCodes()
    {
        return $this->hasMany(ReferralCode::class, 'created_by');
    }

    public function usedReferralCode()
    {
        return $this->hasOne(ReferralCode::class, 'used_by');
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified && !is_null($this->email_verified_at);
    }

    public function markEmailAsVerified(): bool
    {
        return $this->update([
            'email_verified' => true,
            'email_verified_at' => now()
        ]);
    }

}
