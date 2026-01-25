<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\RegisterWithReferralRequest;
use App\Http\Requests\CreateReferralCodeRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Requests\ResendVerificationRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private JWTAuth $jwtAuth
    ) {}

    // Hapus middleware dari constructor, pindah ke route

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login(
            $request->email,
            $request->password
        );

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => $data
        ], 200);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'data' => null
            ], 401);
        }

        if (!$user->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only super admin can register new users',
                'data' => null
            ], 403);
        }

        $data = $this->authService->register($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => $data
        ], 201);
    }

    /**
     * Public registration with referral code
     */
    public function registerWithReferral(RegisterWithReferralRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->registerWithReferral($request->validated());

            return response()->json([
                'success' => true,
                'message' => $data['message'],
                'data' => [
                    'user' => $data['user']
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 422);
        }
    }

    /**
     * Create referral code (super admin only)
     */
    public function createReferralCode(CreateReferralCodeRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->createReferralCode($request->validated());

            return response()->json([
                'success' => true,
                'message' => $data['message'],
                'data' => $data['data']
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 403);
        }
    }

    /**
     * Send referral invitation email
     */
    public function sendReferralInvitation(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'referral_code' => 'required|string'
            ]);

            $sent = $this->authService->sendReferralInvitation(
                $request->email,
                $request->referral_code
            );

            return response()->json([
                'success' => true,
                'message' => 'Invitation sent successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Verify email with token (GET request)
     */
    public function verifyByToken(string $token): JsonResponse
    {
        try {
            $data = $this->authService->verifyEmail($token);

            return response()->json([
                'success' => true,
                'message' => $data['message'] ?? 'Email verified successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Verify email with code (POST request)
     */
    public function verifyEmailWithCode(VerifyEmailRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->verifyEmailWithCode(
                $request->email,
                $request->code
            );

            return response()->json([
                'success' => true,
                'message' => $data['message'] ?? 'Email verified successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Resend verification email
     */
    public function resendVerification(ResendVerificationRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->resendVerification($request->email);

            return response()->json([
                'success' => true,
                'message' => $data['message'],
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Get referral statistics
     */
    public function getReferralStats(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user || !$user->hasRole('super_admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only super admin can view referral stats',
                    'data' => null
                ], 403);
            }

            $data = $this->authService->getReferralStats();

            return response()->json([
                'success' => true,
                'message' => 'Referral statistics retrieved',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Get my referral codes
     */
    public function getMyReferralCodes(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user || !$user->hasRole('super_admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only super admin can view referral codes',
                    'data' => null
                ], 403);
            }

            $filters = $request->only(['role', 'is_active', 'search', 'per_page']);
            $data = $this->authService->getMyReferralCodes($filters);

            return response()->json([
                'success' => true,
                'message' => 'Referral codes retrieved',
                'data' => $data['data'],
                'meta' => $data['meta']
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();
            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage(),
                'data' => null
            ], 403);
        }
    }

    public function refresh(): JsonResponse
    {
        $data = $this->authService->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => $data
        ], 200);
    }

    public function me(): JsonResponse
    {
        $user = $this->authService->me();

        return response()->json([
            'success' => true,
            'message' => 'User data retrieved',
            'data' => [
                'user' => $user
            ]
        ], 200);
    }
}