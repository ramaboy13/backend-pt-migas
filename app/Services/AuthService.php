<?php

namespace App\Services;

use App\Models\User;
use App\Exceptions\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthService
{
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

        return $this->respondWithToken($token);
    }

    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        // Assign role
        try {
            $user->assignRole($data['role']);
        } catch (\Exception $e) {
            // Log::error("Failed to assign role to user: {$user->email}");
            throw new AuthenticationException('Role not found');
        }

        $token = JWTAuth::fromUser($user);

        return $this->respondWithToken($token);
    }

    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    public function refresh(): array
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            throw new AuthenticationException('Could not refresh token');
        }
    }

    public function me(): array
    {
         $user = Auth::user();
        
        if (!$user) {
            throw new AuthenticationException('User not found');
        }

        // Load user with roles for response
        $userWithRoles = User::with('roles')->find($user->id);

        return [
            'id' => $userWithRoles->id,
            'name' => $userWithRoles->name,
            'email' => $userWithRoles->email,
            'roles' => $userWithRoles->getRoleNames(),
            'is_active' => $userWithRoles->is_active,
        ];
    }

    protected function respondWithToken(string $token): array
    {
         $user = Auth::user();
        
        // Load user with roles for response
        $userWithRoles = User::with('roles')->find($user->id);

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60, // in seconds
            'refresh_expires_in' => config('jwt.refresh_ttl') * 60, // in seconds
            'user' => [
                'id' => $userWithRoles->id,
                'name' => $userWithRoles->name,
                'email' => $userWithRoles->email,
                'roles' => $userWithRoles->getRoleNames(),
            ]
        ];
    }
}