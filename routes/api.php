<?php

use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\KasBcaController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('refresh', [AuthController::class, 'refresh']);
});



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
    Route::prefix('assets')->group(function () {
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::get('/', [AssetController::class, 'index']);
            Route::get('/{id}', [AssetController::class, 'show']);
            Route::post('/store', [AssetController::class, 'store']);
            Route::put('/update/{id}', [AssetController::class, 'update']);
            Route::delete('/delete/{id}', [AssetController::class, 'destroy']);
        });
    });
});