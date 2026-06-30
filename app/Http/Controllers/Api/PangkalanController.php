<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PangkalanRequest;
use App\Services\PangkalanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PangkalanController extends Controller
{
    public function __construct(private PangkalanService $pangkalanService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $filters = $request->only(['search']);

            $result = $this->pangkalanService->getAllPangkalan($perPage, $filters);
            $responseData = $result->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Data pangkalan berhasil diambil',
                'data' => $responseData['data'],
                'meta' => $responseData['meta'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pangkalan',
                'data' => null,
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $pangkalan = $this->pangkalanService->getPangkalanById($id);

            if (! $pangkalan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pangkalan tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data pangkalan berhasil diambil',
                'data' => $pangkalan->toArray(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pangkalan',
                'data' => null,
            ], 500);
        }
    }

    public function store(PangkalanRequest $request): JsonResponse
    {
        try {
            $pangkalan = $this->pangkalanService->createPangkalan($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Data pangkalan berhasil ditambahkan',
                'data' => $pangkalan->toArray(),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating Pangkalan record: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data pangkalan',
                'data' => null,
            ], 500);
        }
    }

    public function update(PangkalanRequest $request, string $id): JsonResponse
    {
        try {
            $pangkalan = $this->pangkalanService->updatePangkalan($id, $request->validated());

            if (! $pangkalan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pangkalan tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data pangkalan berhasil diperbarui',
                'data' => $pangkalan->toArray(),
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
                'message' => 'Gagal memperbarui data pangkalan',
                'data' => null,
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->pangkalanService->deletePangkalan($id);

            if (! $deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pangkalan tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data pangkalan berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data pangkalan',
            ], 500);
        }
    }
}
