<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SumberKasRequest;
use App\Services\SumberKasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SumberKasController extends Controller
{
    public function __construct(private SumberKasService $service) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $filters = $request->only(['tipe', 'aktif', 'search']);

            $result = $this->service->getAllSumberKas($filters, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data sumber kas berhasil diambil',
                'data' => $result->items,
                'meta' => $result->meta,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving sumber kas: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data sumber kas',
                'data' => null,
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $sumberKas = $this->service->getSumberKasById($id);

            if (! $sumberKas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sumber kas tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data sumber kas berhasil diambil',
                'data' => $sumberKas->toArray(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving sumber kas: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data sumber kas',
                'data' => null,
            ], 500);
        }
    }

    public function store(SumberKasRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $sumberKas = $this->service->createSumberKas($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data sumber kas berhasil dibuat',
                'data' => $sumberKas->toArray(),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating sumber kas: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat data sumber kas',
                'data' => null,
            ], 500);
        }
    }

    public function update(SumberKasRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $sumberKas = $this->service->updateSumberKas($id, $validated);

            if (! $sumberKas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sumber kas tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data sumber kas berhasil diupdate',
                'data' => $sumberKas->toArray(),
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating sumber kas: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data sumber kas',
                'data' => null,
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->service->deleteSumberKas($id);

            if (! $deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sumber kas tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data sumber kas berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting sumber kas: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data sumber kas',
            ], 500);
        }
    }

    public function restore(string $id): JsonResponse
    {
        try {
            $restored = $this->service->restoreSumberKas($id);

            if (! $restored) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sumber kas tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data sumber kas berhasil dipulihkan',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error restoring sumber kas: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan data sumber kas',
            ], 500);
        }
    }

    public function getBanks(): JsonResponse
    {
        try {
            $banks = $this->service->getBanks();

            return response()->json([
                'success' => true,
                'message' => 'Data bank berhasil diambil',
                'data' => $banks,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving banks: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data bank',
                'data' => null,
            ], 500);
        }
    }

    public function getCash(): JsonResponse
    {
        try {
            $cash = $this->service->getCash();

            return response()->json([
                'success' => true,
                'message' => 'Data cash berhasil diambil',
                'data' => $cash,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving cash: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data cash',
                'data' => null,
            ], 500);
        }
    }

    public function getSaldoSummary(): JsonResponse
    {
        try {
            $summary = $this->service->getSaldoSummary();

            return response()->json([
                'success' => true,
                'message' => 'Ringkasan saldo berhasil diambil',
                'data' => $summary,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving saldo summary: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil ringkasan saldo',
                'data' => null,
            ], 500);
        }
    }

    public function getActiveSumberKas(): JsonResponse
    {
        try {
            $sumberKasList = $this->service->getAllActiveSumberKas();

            return response()->json([
                'success' => true,
                'message' => 'Data sumber kas aktif berhasil diambil',
                'data' => $sumberKasList,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving active sumber kas: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data sumber kas aktif',
                'data' => null,
            ], 500);
        }
    }
}
