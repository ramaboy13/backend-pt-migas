<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KasPerusahaanRequest;
use App\Services\KasPerusahaanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KasPerusahaanController extends Controller
{
    public function __construct(private KasPerusahaanService $service) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $filters = $request->only(['start_date', 'end_date', 'sumber_kas_id', 'tipe_transaksi', 'keterangan']);

            $result = $this->service->getAllKasPerusahaan($filters, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data kas perusahaan berhasil diambil',
                'data' => $result->items,
                'meta' => $result->meta,
            ], 200);
        } catch (\Exception $e) {
            // Log::error('Error retrieving kas perusahaan: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kas perusahaan',
                'data' => null,
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $kasPerusahaan = $this->service->getKasPerusahaanById($id);

            if (! $kasPerusahaan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kas perusahaan tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data kas perusahaan berhasil diambil',
                'data' => $kasPerusahaan->toArray(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving kas perusahaan: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kas perusahaan',
                'data' => null,
            ], 500);
        }
    }

    public function store(KasPerusahaanRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $kasPerusahaan = $this->service->createKasPerusahaan($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data kas perusahaan berhasil dibuat',
                'data' => $kasPerusahaan->toArray(),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating kas perusahaan: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat data kas perusahaan',
                'data' => null,
            ], 500);
        }
    }

    public function update(KasPerusahaanRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $kasPerusahaan = $this->service->updateKasPerusahaan($id, $validated);

            if (! $kasPerusahaan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kas perusahaan tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data kas perusahaan berhasil diupdate',
                'data' => $kasPerusahaan->toArray(),
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating kas perusahaan: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data kas perusahaan',
                'data' => null,
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->service->deleteKasPerusahaan($id);

            if (! $deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kas perusahaan tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data kas perusahaan berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting kas perusahaan: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data kas perusahaan',
            ], 500);
        }
    }

    public function getSaldoPerSumberKas(): JsonResponse
    {
        try {
            $saldoData = $this->service->getSaldoPerSumberKas();

            return response()->json([
                'success' => true,
                'message' => 'Data saldo per sumber kas berhasil diambil',
                'data' => $saldoData,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting saldo per sumber kas: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data saldo per sumber kas',
                'data' => null,
            ], 500);
        }
    }
}
