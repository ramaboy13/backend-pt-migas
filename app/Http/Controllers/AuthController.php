<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

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

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
            'data' => null
        ], 200);
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