<?php
// app/Services/AuthService.php

namespace App\Services;

use App\Models\User;
use App\Models\RefreshToken;
use App\Models\EmailVerification;
use App\Models\ReferralCode;
use App\Exceptions\AuthenticationException;
use App\Mail\EmailVerificationMail;
use App\Mail\ReferralInvitationMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthService
{
    protected EmailVerificationService $emailVerificationService;
    protected ReferralCodeService $referralCodeService;

    public function __construct()
    {
        $this->emailVerificationService = app(EmailVerificationService::class);
        $this->referralCodeService = app(ReferralCodeService::class);
    }

    public function login(string $email, string $password): array
    {
        $credentials = ['email' => $email, 'password' => $password];

        if (!$token = JWTAuth::attempt($credentials)) {
            throw new AuthenticationException('Invalid credentials');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            throw new AuthenticationException('Account is deactivated');
        }

        // Check if email is verified (only for non-super_admin users)
        if (!$user->hasRole('super_admin') && !$user->email_verified) {
            // Optionally, we can throw an exception or just return a warning
            // For now, we'll allow login but include a warning
            Log::warning("User {$user->email} logged in without email verification");
        }

        $refreshToken = $this->generateRefreshToken($user->id);

        return $this->respondWithToken($token, $refreshToken);
    }

    public function register(array $data): array
    {
        // Check if email already exists
        $existingUser = User::where('email', $data['email'])->first();
        if ($existingUser) {
            throw new AuthenticationException('Email already registered');
        }

        // Validate role (only super_admin can create super_admin users)
        $currentUser = Auth::user();
        if (isset($data['role']) && $data['role'] === 'super_admin') {
            if (!$currentUser || !$currentUser->hasRole('super_admin')) {
                throw new AuthenticationException('Only super admin can create super admin users');
            }
        }

        // Create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
            'email_verified' => true, // Super admin created users are auto-verified
            'referred_by' => null,
            'referral_code_used' => null
        ]);

        // Assign role
        try {
            $user->assignRole($data['role'] ?? 'admin');
        } catch (\Exception $e) {
            Log::error("Failed to assign role to user {$user->id}: " . $e->getMessage());
            throw new AuthenticationException('Role assignment failed');
        }

        $token = JWTAuth::fromUser($user);
        $refreshToken = $this->generateRefreshToken($user->id);

        return $this->respondWithToken($token, $refreshToken);
    }

    /**
     * Public registration with referral code
     */
    public function registerWithReferral(array $data): array
    {
        // Validate referral code
        $referralCode = ReferralCode::where('code', $data['referral_code'])
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereColumn('used_count', '<', 'max_uses')
            ->first();

        if (!$referralCode) {
            throw new AuthenticationException('Invalid or expired referral code');
        }

        // Check if email already exists
        $existingUser = User::where('email', $data['email'])->first();
        if ($existingUser) {
            throw new AuthenticationException('Email already registered');
        }

        // Validate Gmail domain (optional)
        if (!$this->isValidGmail($data['email'])) {
            throw new AuthenticationException('Only Gmail addresses are allowed');
        }

        // Create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
            'email_verified' => false, // Need email verification
            'referred_by' => $referralCode->created_by,
            'referral_code_used' => $data['referral_code']
        ]);

        // Assign role from referral code
        try {
            $user->assignRole($referralCode->role);
        } catch (\Exception $e) {
            Log::error("Failed to assign role to user {$user->id}: " . $e->getMessage());
            throw new AuthenticationException('Role assignment failed');
        }

        // Update referral code usage
        $referralCode->increment('used_count');
        $referralCode->update(['used_by' => $user->id]);

        // Send verification email
        $this->sendVerificationEmail($user);

        return [
            'success' => true,
            'message' => 'Registration successful. Please check your email for verification.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $referralCode->role
            ]
        ];
    }

    /**
     * Create referral code (super admin only)
     */
    public function createReferralCode(array $data): array
    {
        $currentUser = Auth::user();
        
        if (!$currentUser || !$currentUser->hasRole('super_admin')) {
            throw new AuthenticationException('Only super admin can create referral codes');
        }

        // Validate role
        $validRoles = ['admin']; // Only admin for now
        if (!in_array($data['role'], $validRoles)) {
            throw new AuthenticationException('Invalid role. Only "admin" is allowed');
        }

        // Generate unique referral code
        do {
            $code = 'REF-' . strtoupper(Str::random(10));
        } while (ReferralCode::where('code', $code)->exists());

        $referralCode = ReferralCode::create([
            'code' => $code,
            'created_by' => $currentUser->id,
            'role' => $data['role'],
            'max_uses' => $data['max_uses'] ?? 1,
            'expires_at' => $data['expires_at'] ?? Carbon::now()->addDays(30),
            'is_active' => true
        ]);

        return [
            'success' => true,
            'message' => 'Referral code created successfully',
            'data' => [
                'code' => $referralCode->code,
                'role' => $referralCode->role,
                'max_uses' => $referralCode->max_uses,
                'expires_at' => $referralCode->expires_at,
                'link' => url("/register?ref={$referralCode->code}")
            ]
        ];
    }

    /**
     * Send referral invitation email
     */
    public function sendReferralInvitation(string $email, string $referralCode): bool
    {
        $currentUser = Auth::user();
        
        if (!$currentUser || !$currentUser->hasRole('super_admin')) {
            throw new AuthenticationException('Only super admin can send invitations');
        }

        $referral = ReferralCode::where('code', $referralCode)
            ->where('created_by', $currentUser->id)
            ->first();

        if (!$referral) {
            throw new AuthenticationException('Referral code not found');
        }

        try {
            Mail::to($email)->send(new ReferralInvitationMail(
                $referral->code,
                $referral->role,
                $referral->expires_at,
                $currentUser->name
            ));

            Log::info("Referral invitation sent to {$email} with code {$referralCode}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send referral invitation to {$email}: " . $e->getMessage());
            throw new AuthenticationException('Failed to send invitation email');
        }
    }

    /**
     * Verify email with token
     */
    public function verifyEmail(string $token): array
    {
        $verification = EmailVerification::where('token', $token)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            throw new AuthenticationException('Invalid or expired verification token');
        }

        $user = User::where('email', $verification->email)->first();
        if (!$user) {
            throw new AuthenticationException('User not found');
        }

        // Mark email as verified
        $user->update([
            'email_verified' => true,
            'email_verified_at' => now()
        ]);

        // Mark verification as used
        $verification->update(['verified_at' => now()]);

        // If user was registered with referral code, update referred_by
        if ($user->referral_code_used && !$user->referred_by) {
            $referralCode = ReferralCode::where('code', $user->referral_code_used)->first();
            if ($referralCode) {
                $user->update(['referred_by' => $referralCode->created_by]);
            }
        }

        // Generate JWT token for auto-login
        $accessToken = JWTAuth::fromUser($user);
        $refreshToken = $this->generateRefreshToken($user->id);

        return array_merge(
            $this->respondWithToken($accessToken, $refreshToken),
            ['message' => 'Email verified successfully']
        );
    }

    /**
     * Verify email with code (alternative method)
     */
    public function verifyEmailWithCode(string $email, string $code): array
    {
        $verification = EmailVerification::where('email', $email)
            ->where('verification_code', $code)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            throw new AuthenticationException('Invalid verification code');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new AuthenticationException('User not found');
        }

        // Mark email as verified
        $user->update([
            'email_verified' => true,
            'email_verified_at' => now()
        ]);

        // Mark verification as used
        $verification->update(['verified_at' => now()]);

        // If user was registered with referral code, update referred_by
        if ($user->referral_code_used && !$user->referred_by) {
            $referralCode = ReferralCode::where('code', $user->referral_code_used)->first();
            if ($referralCode) {
                $user->update(['referred_by' => $referralCode->created_by]);
            }
        }

        // Generate JWT token for auto-login
        $accessToken = JWTAuth::fromUser($user);
        $refreshToken = $this->generateRefreshToken($user->id);

        return array_merge(
            $this->respondWithToken($accessToken, $refreshToken),
            ['message' => 'Email verified successfully']
        );
    }

    /**
     * Resend verification email
     */
    public function resendVerification(string $email): array
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new AuthenticationException('User not found');
        }

        if ($user->email_verified) {
            throw new AuthenticationException('Email already verified');
        }

        $this->sendVerificationEmail($user);

        return [
            'success' => true,
            'message' => 'Verification email sent successfully'
        ];
    }

    /**
     * Send verification email
     */
    protected function sendVerificationEmail(User $user): void
    {
        // Delete any existing unverified tokens
        EmailVerification::where('email', $user->email)
            ->whereNull('verified_at')
            ->delete();

        // Create new verification token
        $token = Str::random(60);
        $verificationCode = rand(100000, 999999); // 6-digit code

        EmailVerification::create([
            'email' => $user->email,
            'token' => $token,
            'verification_code' => $verificationCode,
            'user_id' => $user->id,
            'type' => 'REGISTRATION',
            'expires_at' => Carbon::now()->addHours(24)
        ]);

        $verificationUrl = url("/api/auth/verify-email/{$token}");

        try {
            Mail::to($user->email)->send(new EmailVerificationMail(
                $user->name,
                $verificationUrl,
                $verificationCode
            ));

            Log::info("Verification email sent to {$user->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send verification email to {$user->email}: " . $e->getMessage());
            throw new AuthenticationException('Failed to send verification email');
        }
    }

    public function logout(): void
    {
        $user = Auth::user();

        // Revoke all refresh tokens for this user
        RefreshToken::where('user_id', $user->id)
            ->update(['is_revoked' => true]);

        JWTAuth::invalidate(JWTAuth::getToken());
    }

    public function refresh(): array
    {
        try {
            // Get refresh token from request
            $refreshToken = request()->input('refresh_token');

            if (!$refreshToken) {
                throw new AuthenticationException('Refresh token is required');
            }

            // Validate refresh token
            $storedToken = RefreshToken::where('token', $refreshToken)
                ->where('expires_at', '>', now())
                ->where('is_revoked', false)
                ->first();

            if (!$storedToken) {
                throw new AuthenticationException('Invalid or expired refresh token');
            }

            $user = User::find($storedToken->user_id);

            if (!$user || !$user->is_active) {
                throw new AuthenticationException('User not found or inactive');
            }

            // Generate new access token
            $token = JWTAuth::fromUser($user);

            // Revoke old refresh token
            $storedToken->update(['is_revoked' => true]);

            // Generate new refresh token
            $newRefreshToken = $this->generateRefreshToken($user->id);

            return $this->respondWithToken($token, $newRefreshToken);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            throw new AuthenticationException('Token has expired');
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            throw new AuthenticationException('Token is invalid');
        } catch (\Exception $e) {
            throw new AuthenticationException('Could not refresh token: ' . $e->getMessage());
        }
    }

    public function me(): array
    {
        $user = Auth::user();

        if (!$user) {
            throw new AuthenticationException('User not found');
        }

        $userWithRoles = User::with('roles')->find($user->id);

        return [
            'id' => $userWithRoles->id,
            'name' => $userWithRoles->name,
            'email' => $userWithRoles->email,
            'roles' => $userWithRoles->getRoleNames(),
            'is_active' => $userWithRoles->is_active,
            'email_verified' => $userWithRoles->email_verified,
            'email_verified_at' => $userWithRoles->email_verified_at,
            'referred_by' => $userWithRoles->referred_by,
            'referral_code_used' => $userWithRoles->referral_code_used
        ];
    }

    protected function generateRefreshToken(string $userId): string
    {
        // Delete expired tokens
        RefreshToken::where('expires_at', '<', now())->delete();

        $refreshToken = Str::random(128);

        RefreshToken::create([
            'id' => Str::uuid()->toString(),
            'user_id' => $userId,
            'token' => $refreshToken,
            'expires_at' => now()->addDays(14),
            'is_revoked' => false
        ]);

        return $refreshToken;
    }

    protected function respondWithToken(string $token, string $refreshToken = null): array
    {
        $user = Auth::user();
        $userWithRoles = User::with('roles')->find($user->id);

        $response = [
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => [
                'id' => $userWithRoles->id,
                'name' => $userWithRoles->name,
                'email' => $userWithRoles->email,
                'roles' => $userWithRoles->getRoleNames(),
                'is_active' => $userWithRoles->is_active,
                'email_verified' => $userWithRoles->email_verified
            ]
        ];

        return $response;
    }

    /**
     * Validate Gmail address
     */
    protected function isValidGmail(string $email): bool
    {
        // Basic validation for Gmail
        $pattern = '/^[a-zA-Z0-9._%+-]+@gmail\.com$/i';
        return preg_match($pattern, $email) === 1;
    }

    /**
     * Get referral statistics for current user
     */
    public function getReferralStats(): array
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->hasRole('super_admin')) {
            throw new AuthenticationException('Only super admin can view referral stats');
        }

        $created = ReferralCode::where('created_by', $currentUser->id)->count();
        $used = ReferralCode::where('created_by', $currentUser->id)
            ->where('used_count', '>', 0)
            ->count();
        $active = ReferralCode::where('created_by', $currentUser->id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereColumn('used_count', '<', 'max_uses')
            ->count();

        $referredUsers = User::where('referred_by', $currentUser->id)
            ->with('roles')
            ->get(['id', 'name', 'email', 'created_at', 'email_verified']);

        return [
            'created_codes' => $created,
            'used_codes' => $used,
            'active_codes' => $active,
            'referred_users' => $referredUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->getRoleNames()[0] ?? null,
                    'joined_at' => $user->created_at,
                    'email_verified' => $user->email_verified
                ];
            })
        ];
    }

    /**
     * Get all referral codes created by current user
     */
    public function getMyReferralCodes(array $filters = []): array
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->hasRole('super_admin')) {
            throw new AuthenticationException('Only super admin can view referral codes');
        }

        $query = ReferralCode::where('created_by', $currentUser->id)
            ->with(['creator', 'usedBy']);

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $query->where('code', 'LIKE', '%' . $filters['search'] . '%');
        }

        $paginator = $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 10);

        $items = $paginator->items()->map(function ($code) {
            return [
                'id' => $code->id,
                'code' => $code->code,
                'role' => $code->role,
                'created_by' => $code->creator->name ?? 'Unknown',
                'used_by' => $code->usedBy->name ?? null,
                'max_uses' => $code->max_uses,
                'used_count' => $code->used_count,
                'expires_at' => $code->expires_at,
                'is_active' => $code->is_active,
                'is_valid' => $code->is_active && 
                    ($code->expires_at === null || $code->expires_at > now()) &&
                    $code->used_count < $code->max_uses,
                'created_at' => $code->created_at
            ];
        })->toArray();

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
}