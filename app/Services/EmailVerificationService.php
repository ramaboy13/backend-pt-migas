<?php
// app/Services/EmailVerificationService.php

namespace App\Services;

use App\Repositories\EmailVerificationRepository;
use App\Repositories\UserRepository;
use App\Mail\EmailVerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailVerificationService
{
    public function __construct(
        private EmailVerificationRepository $verificationRepository,
        private UserRepository $userRepository
    ) {}

    public function sendVerificationEmail(string $email, string $name, string $type = 'REGISTRATION'): array
    {
        try {
            // Cek jika user sudah ada dan sudah verified
            $user = $this->userRepository->findByEmail($email);
            if ($user && $user->hasVerifiedEmail()) {
                throw new \InvalidArgumentException('Email sudah terverifikasi');
            }

            // Create verification record
            $verification = $this->verificationRepository->createVerification(
                $email, 
                $type,
                $user?->id
            );

            // Kirim email
            $verificationUrl = url("/api/auth/verify-email/{$verification->token}");
            
            Mail::to($email)->send(new EmailVerificationMail(
                $name,
                $verificationUrl,
                $verification->verification_code
            ));

            Log::info("Verification email sent to {$email}");

            return [
                'email' => $email,
                'verification_sent' => true,
                'expires_at' => $verification->expires_at
            ];

        } catch (\Exception $e) {
            Log::error("Failed to send verification email to {$email}: " . $e->getMessage());
            throw new \RuntimeException('Gagal mengirim email verifikasi: ' . $e->getMessage());
        }
    }

    public function verifyByToken(string $token): ?array
    {
        $verification = $this->verificationRepository->verifyToken($token);
        
        if (!$verification) {
            return null;
        }

        // Update user email verification status
        if ($verification->user_id) {
            $user = $this->userRepository->findById($verification->user_id);
            if ($user) {
                $user->markEmailAsVerified();
            }
        }

        return [
            'email' => $verification->email,
            'verified_at' => $verification->verified_at,
            'type' => $verification->type
        ];
    }

    public function verifyByCode(string $email, string $code): ?array
    {
        $verification = $this->verificationRepository->verifyCode($email, $code);
        
        if (!$verification) {
            return null;
        }

        // Update user email verification status
        if ($verification->user_id) {
            $user = $this->userRepository->findById($verification->user_id);
            if ($user) {
                $user->markEmailAsVerified();
            }
        }

        return [
            'email' => $verification->email,
            'verified_at' => $verification->verified_at,
            'type' => $verification->type
        ];
    }

    public function resendVerification(string $email): array
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            throw new \InvalidArgumentException('User tidak ditemukan');
        }

        if ($user->hasVerifiedEmail()) {
            throw new \InvalidArgumentException('Email sudah terverifikasi');
        }

        return $this->sendVerificationEmail($email, $user->name, 'REGISTRATION');
    }

    public function cleanupExpiredVerifications(): int
    {
        return $this->verificationRepository->deleteExpiredVerifications();
    }
}