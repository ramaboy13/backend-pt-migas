<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetRequest;
use App\Services\AssetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function __construct(private AssetService $service) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $filters = $request->only(['status', 'search']);

            $assets = $this->service->getAllAssets($filters, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data aset berhasil diambil',
                'data' => $assets->items(),
                'meta' => [
                    'current_page' => $assets->currentPage(),
                    'per_page' => $assets->perPage(),
                    'total' => $assets->total(),
                    'last_page' => $assets->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data aset: '.$e->getMessage(),
                'data' => null,
                'meta' => null,
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $asset = $this->service->getAssetById($id);

            if (! $asset) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data aset tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data aset berhasil diambil',
                'data' => $asset,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data aset',
                'data' => null,
            ], 500);
        }
    }

    public function store(AssetRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $asset = $this->service->createAsset($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data aset berhasil ditambahkan',
                'data' => $asset,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data aset',
                'data' => null,
            ], 500);
        }
    }

    public function update(AssetRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $asset = $this->service->updateAsset($id, $validated);

            if (! $asset) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data aset tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data aset berhasil diperbarui',
                'data' => $asset,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data aset',
                'data' => null,
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->service->deleteAsset($id);

            if (! $deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data aset tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data aset berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data aset',
            ], 500);
        }
    }
}
