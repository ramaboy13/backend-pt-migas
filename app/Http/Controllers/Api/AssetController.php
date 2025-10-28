<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetRequest;
use App\Services\AssetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AssetController extends Controller
{
    public function __construct(private AssetService $assetService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $filters = $request->only(['its_rfu', 'search']);
            $result = $this->assetService->getAllAssets($filters, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Assets retrieved successfully',
                'data' => $result['data'],
                'meta' => $result['data']['pagination'] ?? null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve assets',
                'data' => null
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $result = $this->assetService->getAssetById($id);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Asset retrieved successfully',
                'data' => $result['data']
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve asset',
                'data' => null
            ], 500);
        }
    }

    public function store(AssetRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $result = $this->assetService->createAsset($validated);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'data' => null
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Asset created successfully',
                'data' => $result['data']
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create asset',
                'data' => null
            ], 500);
        }
    }

    public function update(AssetRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $result = $this->assetService->updateAsset($id, $validated);

            if (!$result['success']) {
                $statusCode = $result['message'] === 'Asset not found' ? 404 : 422;
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'data' => null
                ], $statusCode);
            }

            return response()->json([
                'success' => true,
                'message' => 'Asset updated successfully',
                'data' => $result['data']
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update asset',
                'data' => null
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {

            $result = $this->assetService->deleteAsset($id);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Asset deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete asset',
            ], 500);
        }
    }
}
