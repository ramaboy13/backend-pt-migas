<?php

use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\KaryawanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\KasBcaController;
use App\Http\Controllers\Api\LemburKaryawanController;
use App\Http\Controllers\Api\PangkalanController;
use App\Http\Controllers\Api\PendapatanController;
use App\Http\Controllers\Api\PotonganController;
use App\Http\Controllers\Api\TabungController;
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

    // Pangkalan Routes
    Route::prefix('pangkalan')->group(function () {
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::get('/', [PangkalanController::class, 'index']);
            Route::post('/create', [PangkalanController::class, 'store']);
            Route::get('/show/{id}', [PangkalanController::class, 'show']);
            Route::put('/update/{id}', [PangkalanController::class, 'update']);
            Route::delete('/delete/{id}', [PangkalanController::class, 'destroy']);
        });
    });

    //Routes Lembur Karyawan
    Route::prefix('lembur-karyawan')->group(function () {
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::get('/', [LemburKaryawanController::class, 'index']);
            Route::post('/create', [LemburKaryawanController::class, 'store']);
            Route::get('/show/{id}', [LemburKaryawanController::class, 'show']);
            Route::put('/update/{id}', [LemburKaryawanController::class, 'update']);
            Route::delete('/delete/{id}', [LemburKaryawanController::class, 'destroy']);
            Route::get('/karyawan/{karyawanId}', [LemburKaryawanController::class, 'getByKaryawan']);
        });
    });

    //Routes Pendapatan
    Route::prefix('pendapatan')->group(function () {
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::get('/', [PendapatanController::class, 'index']);
            Route::post('/create', [PendapatanController::class, 'store']);
            Route::get('/show/{id}', [PendapatanController::class, 'show']);
            Route::put('/update/{id}', [PendapatanController::class, 'update']);
            Route::delete('/delete/{id}', [PendapatanController::class, 'destroy']);
            Route::get('/karyawan/{karyawanId}', [PendapatanController::class, 'getByKaryawan']);
            Route::get('/periode', [PendapatanController::class, 'getByPeriode']);
            Route::post('/recalculate', [PendapatanController::class, 'recalculate']);
        });
    });

    // Potongan Routes
    Route::prefix('potongan')->group(function () {
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::get('/', [PotonganController::class, 'index']);
            Route::post('/create', [PotonganController::class, 'store']);
            Route::get('/show/{id}', [PotonganController::class, 'show']);
            Route::put('/update/{id}', [PotonganController::class, 'update']);
            Route::delete('/delete/{id}', [PotonganController::class, 'destroy']);
            Route::get('/karyawan/{karyawanId}', [PotonganController::class, 'getByKaryawan']);
            Route::get('/periode', [PotonganController::class, 'getByPeriode']);
            Route::get('/total-periode', [PotonganController::class, 'getTotalByPeriode']);
            Route::post('/recalculate', [PotonganController::class, 'recalculate']);
        });
    });
});
