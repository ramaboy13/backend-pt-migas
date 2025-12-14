<?php

use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\GajiKaryawanController;
use App\Http\Controllers\Api\KaryawanController;
use App\Http\Controllers\Api\KasPerusahaanController;
use App\Http\Controllers\Api\LemburKaryawanController;
use App\Http\Controllers\Api\PangkalanController;
use App\Http\Controllers\Api\PendapatanController;
use App\Http\Controllers\Api\PotonganController;
use App\Http\Controllers\Api\SumberKasController;
use App\Http\Controllers\Api\TabungController;
use App\Http\Controllers\Api\TransaksiOperasionalController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('refresh', [AuthController::class, 'refresh']);
});
Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'time' => now(),
    ]);
});

Route::middleware(['auth:api'])->group(function () {

    // Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->middleware('role:super_admin');
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Kas BCA Routes
    Route::prefix('kas-perusahaan')->group(function () {
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::get('/', [KasPerusahaanController::class, 'index']);
            Route::post('/create', [KasPerusahaanController::class, 'store']);
            Route::get('/show/{id}', [KasPerusahaanController::class, 'show']);
            Route::put('/update/{id}', [KasPerusahaanController::class, 'update']);
            Route::delete('/delete/{id}', [KasPerusahaanController::class, 'destroy']);
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

    // Routes Lembur Karyawan
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

    // Routes Pendapatan
    Route::prefix('pendapatan')->group(function () {
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::get('/', [PendapatanController::class, 'index']);
            Route::post('/create', [PendapatanController::class, 'store']);
            Route::get('/show/{id}', [PendapatanController::class, 'show']);
            Route::put('/update/{id}', [PendapatanController::class, 'update']);
            Route::delete('/delete/{id}', [PendapatanController::class, 'destroy']);
            Route::get('/karyawan/{karyawanId}', [PendapatanController::class, 'getByKaryawan']);
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
            Route::post('/recalculate', [PotonganController::class, 'recalculate']);
        });
    });

    // Gaji Karyawan Routes
    Route::prefix('gaji-karyawan')->group(function () {
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::get('/', [GajiKaryawanController::class, 'index']);
            Route::post('/create', [GajiKaryawanController::class, 'store']);
            Route::get('/show/{id}', [GajiKaryawanController::class, 'show']);
            Route::put('/update/{id}', [GajiKaryawanController::class, 'update']);
            Route::delete('/delete/{id}', [GajiKaryawanController::class, 'destroy']);
            Route::get('/karyawan/{karyawanId}', [GajiKaryawanController::class, 'getByKaryawan']);
            Route::post('/recalculate', [GajiKaryawanController::class, 'recalculate']);
        });
    });

    // Transaksi Operasional Routes
    Route::prefix('transaksi-operasional')->group(function () {
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::get('/', [TransaksiOperasionalController::class, 'index']);
            Route::post('/create', [TransaksiOperasionalController::class, 'store']);
            Route::get('/show/{id}', [TransaksiOperasionalController::class, 'show']);
            Route::put('/update/{id}', [TransaksiOperasionalController::class, 'update']);
            Route::delete('/delete/{id}', [TransaksiOperasionalController::class, 'destroy']);
            Route::get('/summary', [TransaksiOperasionalController::class, 'getSummary']);
            Route::prefix('pdf')->group(function () {
                Route::get('/report', [TransaksiOperasionalController::class, 'generatePdfReport']);
                Route::get('/pangkalan/{pangkalanId}', [TransaksiOperasionalController::class, 'generatePdfPangkalan']);
                Route::get('/download-correct/{filename}', [TransaksiOperasionalController::class, 'downloadPdfCorrect']);
                Route::get('/preview-correct/{filename}', [TransaksiOperasionalController::class, 'previewPdfCorrect']);
                Route::get('/list', [TransaksiOperasionalController::class, 'listPdfFiles']);
            });
        });
    });

    // User Management Routes
    Route::prefix('users')->group(function () {
        Route::middleware(['role:super_admin'])->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/create', [UserController::class, 'store']);
            Route::get('/show/{id}', [UserController::class, 'show']);
            Route::put('/update/{id}', [UserController::class, 'update']);
            Route::delete('/delete/{id}', [UserController::class, 'destroy']);
            Route::post('/{id}/deactivate', [UserController::class, 'deactivate']);
            Route::post('/{id}/activate', [UserController::class, 'activate']);
        });
    });

    Route::prefix('kas-perusahaan')->group(function () {
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::get('/', [KasPerusahaanController::class, 'index']);
            Route::post('/create', [KasPerusahaanController::class, 'store']);
            Route::get('/show/{id}', [KasPerusahaanController::class, 'show']);
            Route::put('/update/{id}', [KasPerusahaanController::class, 'update']);
            Route::delete('/delete/{id}', [KasPerusahaanController::class, 'destroy']);
            Route::get('/saldo-per-sumber-kas', [KasPerusahaanController::class, 'getSaldoPerSumberKas']);
        });
    });
    Route::prefix('sumber-kas')->group(function () {
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::get('/', [SumberKasController::class, 'index']);
            Route::post('/create', [SumberKasController::class, 'store']);
            Route::get('/show/{id}', [SumberKasController::class, 'show']);
            Route::put('/update/{id}', [SumberKasController::class, 'update']);
            Route::delete('/delete/{id}', [SumberKasController::class, 'destroy']);
            Route::post('/restore/{id}', [SumberKasController::class, 'restore']);
            Route::get('/banks', [SumberKasController::class, 'getBanks']);
            Route::get('/cash', [SumberKasController::class, 'getCash']);
            Route::get('/active', [SumberKasController::class, 'getActiveSumberKas']);
            Route::get('/saldo-summary', [SumberKasController::class, 'getSaldoSummary']);
        });
    });
});
