<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\KasBcaController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('refresh', [AuthController::class, 'refresh']);
});

// Simple login route untuk handle redirect (Laravel 11 requirement)
Route::get('/login', function () {
    return response()->json([
        'success' => false,
        'message' => 'Unauthenticated. Please login first.',
        'data' => null
    ], 401);
})->name('login');


Route::middleware(['auth:api'])->group(function () {
    
    // Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->middleware('role:super_admin');
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Kas BCA Routes
    Route::prefix('kas-bca')->group(function () {
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::get('/', [KasBcaController::class, 'index']);
            Route::get('/{id}', [KasBcaController::class, 'show']);
            // Route::get('/date-range', [KasBcaController::class, 'getByDateRange']);
            Route::post('/store', [KasBcaController::class, 'store']);
            Route::put('/update/{id}', [KasBcaController::class, 'update']);
            Route::delete('/delete/{id}', [KasBcaController::class, 'destroy']);
        });
    });
});