<?php
// app/Services/ReferralCodeService.php

namespace App\Services;

use App\Repositories\ReferralCodeRepository;
use App\Repositories\UserRepository;
use App\Mail\ReferralInvitationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class ReferralCodeService
{
    public function __construct(
        private ReferralCodeRepository $referralRepository,
        private UserRepository $userRepository
    ) {}

    public function createReferralCode(array $data): array
    {
        $currentUser = Auth::user();
        
        if (!$currentUser || !$currentUser->hasRole('super_admin')) {
            throw new \InvalidArgumentException('Hanya Super Admin yang bisa membuat referral code');
        }

        // Validate role
        $validRoles = ['admin']; // Hanya untuk admin saja untuk sekarang
        if (!in_array($data['role'], $validRoles)) {
            throw new \InvalidArgumentException('Role tidak valid. Hanya "admin" yang diperbolehkan');
        }

        $referralCode = $this->referralRepository->createCode([
            'created_by' => $currentUser->id,
            'role' => $data['role'],
            'max_uses' => $data['max_uses'] ?? 1,
            'expires_at' => $data['expires_at'] ?? now()->addDays(30),
            'is_active' => true
        ]);

        return [
            'id' => $referralCode->id,
            'code' => $referralCode->code,
            'role' => $referralCode->role,
            'max_uses' => $referralCode->max_uses,
            'expires_at' => $referralCode->expires_at,
            'created_by' => $currentUser->name,
            'link' => url("/register?ref={$referralCode->code}")
        ];
    }

    public function validateReferralCode(string $code): array
    {
        $referralCode = $this->referralRepository->findActiveByCode($code);
        
        if (!$referralCode) {
            throw new \InvalidArgumentException('Kode referral tidak valid atau sudah tidak aktif');
        }

        return [
            'code' => $referralCode->code,
            'role' => $referralCode->role,
            'created_by' => $referralCode->creator->name ?? 'Unknown',
            'expires_at' => $referralCode->expires_at,
            'remaining_uses' => $referralCode->max_uses - $referralCode->used_count
        ];
    }

    public function useReferralCode(string $code, string $userId): bool
    {
        $referralCode = $this->referralRepository->findActiveByCode($code);
        
        if (!$referralCode) {
            throw new \InvalidArgumentException('Kode referral tidak valid');
        }

        return $referralCode->useCode($userId);
    }

    public function sendReferralInvitation(string $email, array $referralData): bool
    {
        try {
            Mail::to($email)->send(new ReferralInvitationMail(
                $referralData['code'],
                $referralData['role'],
                $referralData['expires_at'],
                $referralData['created_by']
            ));

            return true;
        } catch (\Exception $e) {
            throw new \RuntimeException('Gagal mengirim email undangan: ' . $e->getMessage());
        }
    }

    public function getAllReferralCodes(array $filters = []): array
    {
        $currentUser = Auth::user();
        
        // Jika bukan super_admin, hanya bisa lihat kode yang dia buat
        if (!$currentUser->hasRole('super_admin')) {
            $filters['created_by'] = $currentUser->id;
        }

        $paginator = $this->referralRepository->getAll($filters);
        
        $items = array_map(function ($code) {
    return (object) [
        'id' => $code['id'],
        'code' => $code['code'],
        'role' => $code['role'],
        'created_by' => $code['creator']['name'] ?? 'Unknown',
        'used_by' => $code['usedBy']['name'] ?? null,
        'max_uses' => $code['max_uses'],
        'used_count' => $code['used_count'],
        'expires_at' => $code['expires_at'],
        'is_active' => $code['is_active'],
    ];
}, $paginator->items());

        return [
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage()
            ]
        ];
    }

    public function getReferralStats(): array
    {
        $currentUser = Auth::user();
        return $this->referralRepository->getStatsByUser($currentUser->id);
    }
}