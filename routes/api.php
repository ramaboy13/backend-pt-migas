<?php

use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\KaryawanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\KasBcaController;
use App\Http\Controllers\Api\TabungController;
use Illuminate\Support\Facades\Log;
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
            Route::post('/create', [KasBcaController::class, 'store']);
            Route::get('/show/{id}', [KasBcaController::class, 'show']);
            // Route::get('/date-range', [KasBcaController::class, 'getByDateRange']);
            Route::put('/update/{id}', [KasBcaController::class, 'update']);
            Route::delete('/delete/{id}', [KasBcaController::class, 'destroy']);
        });
    });

    // Asset Routes
    Route::prefix('assets')->group(function () {
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::get('/', [AssetController::class, 'index']);
            Route::post('/create', [AssetController::class, 'store']);
            Route::get('/show/{id}', [AssetController::class, 'show']);
            Route::put('/update/{id}', [AssetController::class, 'update']);
            Route::delete('/delete/{id}', [AssetController::class, 'destroy']);
        });
    });

    // Karyawan Routes
    Route::prefix('karyawan')->group(function () {
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::get('/', [KaryawanController::class, 'index']);
            Route::post('/create', [KaryawanController::class, 'store']);
            Route::get('/show/{id}', [KaryawanController::class, 'show']);
            Route::put('/update/{id}', [KaryawanController::class, 'update']);
            Route::delete('/delete/{id}', [KaryawanController::class, 'destroy']);
        });
    });

    // Tabung Routes
    Route::prefix('tabung')->group(function () {
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::get('/', [TabungController::class, 'index']);
            Route::post('/create', [TabungController::class, 'store']);
            Route::get('/show/{id}', [TabungController::class, 'show']);
            Route::put('/update/{id}', [TabungController::class, 'update']);
            Route::delete('/delete/{id}', [TabungController::class, 'destroy']);
        });
    });
});
