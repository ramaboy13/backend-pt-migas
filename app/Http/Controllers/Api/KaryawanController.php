<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KaryawanRequest;
use App\Services\KaryawanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function __construct(private KaryawanService $service) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $filters = $request->only(['nama', 'jabatan', 'is_active']);

            $result = $this->service->getAllKaryawan($filters, $perPage);
            $responseData = $result->toArray();

            return response()->json([
                'success' => true,
                'data' => $responseData['data'],
                'meta' => $responseData['meta']
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data karyawan',
                'data' => null
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $karyawan = $this->service->getKaryawanById($id);

            if (!$karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan tidak ditemukan',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $karyawan->toArray()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data karyawan',
                'data' => null
            ], 500);
        }
    }

    public function store(KaryawanRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $karyawan = $this->service->createKaryawan($validated);

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil dibuat',
                'data' => $karyawan->toArray()
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat karyawan',
                'data' => null
            ], 500);
        }
    }

    public function update(KaryawanRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $karyawan = $this->service->updateKaryawan($id, $validated);

            if (!$karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan tidak ditemukan',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil diupdate',
                'data' => $karyawan->toArray()
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate karyawan',
                'data' => null
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->service->deleteKaryawan($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus karyawan',
            ], 500);
        }
    }
}
