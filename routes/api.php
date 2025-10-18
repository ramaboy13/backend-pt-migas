<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    
    // Protected Routes
    Route::middleware('auth:api')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

// Test route untuk cek role protection (sementara public)
Route::get('/test-users', function () {
    $users = \App\Models\User::with('roles')->get();
    
    return response()->json([
        'success' => true,
        'message' => 'Users retrieved',
        'data' => ['users' => $users]
    ]);
});