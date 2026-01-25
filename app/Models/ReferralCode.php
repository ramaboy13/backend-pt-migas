<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReferralCode extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'code',
        'created_by',
        'used_by',
        'role',
        'max_uses',
        'used_count',
        'expires_at',
        'is_active'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    // Relationship dengan creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship dengan user yang pakai
    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    // Scope untuk kode yang aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    })
                    ->whereColumn('used_count', '<', 'max_uses');
    }

    // Cek apakah kode masih valid
    public function isValid(): bool
    {
        return $this->is_active && 
               ($this->expires_at === null || $this->expires_at > now()) &&
               $this->used_count < $this->max_uses;
    }

    // Gunakan kode referral
    public function useCode(string $userId): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        return $this->update([
            'used_by' => $userId,
            'used_count' => $this->used_count + 1
        ]);
    }

    // Generate kode referral random
    public static function generateCode(): string
    {
        do {
            $code = 'REF-' . strtoupper(\Illuminate\Support\Str::random(10));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}