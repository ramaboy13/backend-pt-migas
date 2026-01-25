<?php

namespace App\Repositories;

use App\Models\EmailVerification;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmailVerificationRepository
{
    public function __construct(private EmailVerification $model) {}

    public function createVerification(string $email, string $type = 'REGISTRATION', ?string $userId = null): EmailVerification
    {
        // Delete any existing unverified tokens for this email
        $this->model->where('email', $email)
                    ->whereNull('verified_at')
                    ->delete();

        $token = Str::random(60);
        $verificationCode = rand(100000, 999999); // 6-digit code

        return $this->model->create([
            'email' => $email,
            'token' => $token,
            'verification_code' => $verificationCode,
            'user_id' => $userId,
            'type' => $type,
            'expires_at' => Carbon::now()->addHours(24) // 24 jam expiry
        ]);
    }

    public function findByToken(string $token): ?EmailVerification
    {
        return $this->model->where('token', $token)->first();
    }

    public function findByEmailAndCode(string $email, string $code): ?EmailVerification
    {
        return $this->model->where('email', $email)
                          ->where('verification_code', $code)
                          ->valid()
                          ->first();
    }

    public function verifyToken(string $token): ?EmailVerification
    {
        $verification = $this->findByToken($token);
        
        if (!$verification || $verification->isExpired() || $verification->isVerified()) {
            return null;
        }

        $verification->markAsVerified();
        return $verification;
    }

    public function verifyCode(string $email, string $code): ?EmailVerification
    {
        $verification = $this->findByEmailAndCode($email, $code);
        
        if (!$verification) {
            return null;
        }

        $verification->markAsVerified();
        return $verification;
    }

    public function deleteExpiredVerifications(): int
    {
        return $this->model->where('expires_at', '<', now())
                          ->orWhere('verified_at', '<', now()->subDays(7))
                          ->delete();
    }
}