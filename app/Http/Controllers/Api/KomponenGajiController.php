<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KomponenGajiRequest;
use App\Services\KomponenGajiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KomponenGajiController extends Controller
{
    public function __construct(private KomponenGajiService $service) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $filters = $request->only([
                'tipe', 'tanggal', 'start_date', 'end_date',
                'bulan', 'tahun', 'karyawan_id', 'search',
            ]);
            $withKaryawan = $request->boolean('with_karyawan', true);

            $result = $this->service->getAllKomponen($filters, $perPage, $withKaryawan);
            $responseData = $result->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Data komponen gaji berhasil diambil',
                'data' => $responseData['data'],
                'meta' => $responseData['meta'],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving komponen gaji: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data komponen gaji',
                'data' => null,
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $komponen = $this->service->getKomponenById($id);

            if (! $komponen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data komponen gaji tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data komponen gaji berhasil diambil',
                'data' => $komponen->toArray(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving komponen gaji: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data komponen gaji',
                'data' => null,
            ], 500);
        }
    }

    public function store(KomponenGajiRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $komponen = $this->service->createKomponen($validated);

            return response()->json([
                'success' => true,
                'message' => 'Komponen gaji berhasil dibuat',
                'data' => $komponen->toArray(),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating komponen gaji: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat komponen gaji',
                'data' => null,
            ], 500);
        }
    }

    public function update(KomponenGajiRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $komponen = $this->service->updateKomponen($id, $validated);

            if (! $komponen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data komponen gaji tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Komponen gaji berhasil diupdate',
                'data' => $komponen->toArray(),
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating komponen gaji: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate komponen gaji',
                'data' => null,
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->service->deleteKomponen($id);

            if (! $deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data komponen gaji tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Komponen gaji berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting komponen gaji: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus komponen gaji',
            ], 500);
        }
    }

    public function getByKaryawan(Request $request, string $karyawanId): JsonResponse
    {
        try {
            $filters = $request->only(['tipe', 'start_date', 'end_date', 'bulan', 'tahun']);
            $perPage = $request->input('per_page', 10);
            $filters['per_page'] = $perPage;

            $result = $this->service->getKomponenByKaryawan($karyawanId, $filters);
            $responseData = $result->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Data komponen gaji karyawan berhasil diambil',
                'data' => $responseData['data'],
                'meta' => $responseData['meta'],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving komponen gaji by karyawan: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data komponen gaji karyawan',
                'data' => null,
            ], 500);
        }
    }
}
