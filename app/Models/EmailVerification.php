<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailVerification extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'email',
        'token',
        'verification_code',
        'user_id',
        'type',
        'verified_at',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime'
    ];

    // Relationship dengan User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope untuk token yang masih valid
    public function scopeValid($query)
    {
        return $query->whereNull('verified_at')
                    ->where('expires_at', '>', now());
    }

    // Cek apakah token sudah expired
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    // Cek apakah sudah diverifikasi
    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    // Mark sebagai verified
    public function markAsVerified(): bool
    {
        return $this->update([
            'verified_at' => now(),
            'verification_code' => null
        ]);
    }
}