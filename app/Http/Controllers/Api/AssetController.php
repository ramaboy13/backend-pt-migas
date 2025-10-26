<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetRequest;
use App\Services\AssetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    protected $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * Get all assets
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['its_rfu', 'search']);
        $result = $this->assetService->getAllAssets($filters);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get specific asset
     */
    public function show(string $id): JsonResponse
    {
        $result = $this->assetService->getAssetById($id);

        return response()->json($result, $result['success'] ? 200 : 404);
    }

    /**
     * Create new asset
     */
    public function store(AssetRequest $request): JsonResponse
    {
        $result = $this->assetService->createAsset($request->validated());

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Update asset
     */
    public function update(AssetRequest $request, string $id): JsonResponse
    {
        $result = $this->assetService->updateAsset($id, $request->validated());

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Delete asset
     */
    public function destroy(string $id): JsonResponse
    {
        $result = $this->assetService->deleteAsset($id);

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}