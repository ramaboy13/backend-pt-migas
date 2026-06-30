<?php

namespace App\Services;

use App\Exceptions\AuthenticationException;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function login(string $email, string $password): array
    {
        $userCheck = User::where('email', $email)->first();
        if (! $userCheck) {
            throw new AuthenticationException('Email tidak terdaftar', 404);
        }

        $credentials = ['email' => $email, 'password' => $password];

        if (! $token = JWTAuth::attempt($credentials)) {
            throw new AuthenticationException('Kata sandi yang Anda masukkan salah', 401);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            throw new AuthenticationException('Account is deactivated');
        }

        $refreshToken = $this->generateRefreshToken($user->id);

        return $this->respondWithToken($token, $refreshToken);
    }

    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        // Kasih role
        try {
            $user->assignRole($data['role']);
        } catch (\Exception $e) {
            throw new AuthenticationException('Role not found');
        }

        $token = JWTAuth::fromUser($user);
        $refreshToken = $this->generateRefreshToken($user->id);

        return $this->respondWithToken($token, $refreshToken);
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
            // Ambil refresh token dari request
            $refreshToken = request()->input('refresh_token');

            if (! $refreshToken) {
                throw new AuthenticationException('Refresh token is required');
            }

            // Validasi refresh token
            $storedToken = RefreshToken::where('token', $refreshToken)
                ->where('expires_at', '>', now())
                ->where('is_revoked', false)
                ->first();

            if (! $storedToken) {
                throw new AuthenticationException('Invalid or expired refresh token');
            }

            $user = User::find($storedToken->user_id);

            if (! $user || ! $user->is_active) {
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
            throw new AuthenticationException('Could not refresh token');
        }
    }

    public function me(): array
    {
        $user = Auth::user();

        if (! $user) {
            throw new AuthenticationException('User not found');
        }

        $userWithRoles = User::with('roles')->find($user->id);

        return [
            'id' => $userWithRoles->id,
            'name' => $userWithRoles->name,
            'email' => $userWithRoles->email,
            'roles' => $userWithRoles->getRoleNames(),
            'is_active' => $userWithRoles->is_active,
        ];
    }

    protected function generateRefreshToken(string $userId): string
    {
        // Hapus token yang sudah expired
        RefreshToken::where('expires_at', '<', now())->delete();

        $refreshToken = Str::random(128);

        RefreshToken::create([
            'id' => Str::uuid()->toString(),
            'user_id' => $userId,
            'token' => $refreshToken,
            'expires_at' => now()->addDays(14),
            'is_revoked' => false,
        ]);

        return $refreshToken;
    }

    protected function respondWithToken(string $token, ?string $refreshToken = null): array
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
            ],
        ];

        return $response;
    }
}
