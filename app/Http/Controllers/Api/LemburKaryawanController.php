<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LemburKaryawanRequest;
use App\Services\LemburKaryawanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LemburKaryawanController extends Controller
{
    public function __construct(private LemburKaryawanService $service) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $filters = $request->only(['periode', 'start_date', 'end_date', 'search']);
            $withKaryawan = $request->boolean('with_karyawan', true);

            $result = $this->service->getAllLembur($filters, $perPage, $withKaryawan);
            $responseData = $result->toArray();

            return response()->json([
                'success' => true,
                'data' => $responseData['data'],
                'meta' => $responseData['meta'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data lembur',
                'data' => null,
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $lembur = $this->service->getLemburById($id);

            if (! $lembur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data lembur tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $lembur->toArray(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data lembur',
                'data' => null,
            ], 500);
        }
    }

    public function store(LemburKaryawanRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $lembur = $this->service->createLembur($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data lembur berhasil dibuat',
                'data' => $lembur->toArray(),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat data lembur',
                'data' => null,
            ], 500);
        }
    }

    public function update(LemburKaryawanRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $lembur = $this->service->updateLembur($id, $validated);

            if (! $lembur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data lembur tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data lembur berhasil diupdate',
                'data' => $lembur->toArray(),
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data lembur',
                'data' => null,
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->service->deleteLembur($id);

            if (! $deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data lembur tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data lembur berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data lembur',
            ], 500);
        }
    }

    public function getByKaryawan(Request $request, string $karyawanId): JsonResponse
    {
        try {
            $filters = $request->only(['start_date', 'end_date']);
            $perPage = $request->input('per_page', 10);
            $withKaryawan = $request->boolean('with_karyawan', false);

            $result = $this->service->getLemburByKaryawan(
                $karyawanId,
                array_merge($filters, ['per_page' => $perPage]),
                $withKaryawan
            );

            $responseData = $result->toArray();

            return response()->json([
                'success' => true,
                'data' => $responseData['data'],
                'meta' => $responseData['meta'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data lembur karyawan',
                'data' => null,
            ], 500);
        }
    }
}
