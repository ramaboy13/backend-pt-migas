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
      $filters = $request->only(['nama']);

      $result = $this->pangkalanService->getAllPangkalan($perPage, $filters);
      $responseData = $result->toArray();

      return response()->json([
        'success' => true,
        'message' => 'Pangkalan records retrieved successfully',
        'data' => $responseData['data'],
        'meta' => $responseData['meta']
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve pangkalan records',
        'data' => null
      ], 500);
    }
  }

  public function show(string $id): JsonResponse
  {
    try {
      $pangkalan = $this->pangkalanService->getPangkalanById($id);

      if (!$pangkalan) {
        return response()->json([
          'success' => false,
          'message' => 'Pangkalan record not found',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Pangkalan record retrieved successfully',
        'data' => $pangkalan->toArray()
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve pangkalan record',
        'data' => null
      ], 500);
    }
  }

  public function store(PangkalanRequest $request): JsonResponse
  {
    try {
      $pangkalan = $this->pangkalanService->createPangkalan($request->validated());

      return response()->json([
        'success' => true,
        'message' => 'Pangkalan record created successfully',
        'data' => $pangkalan->toArray()
      ], 201);
    } catch (\InvalidArgumentException $e) {
      return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
      ], 422);
    } catch (\Exception $e) {
      Log::error('Error creating Pangkalan record: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Failed to create pangkalan record',
        'data' => null
      ], 500);
    }
  }

  public function update(PangkalanRequest $request, string $id): JsonResponse
  {
    try {
      $pangkalan = $this->pangkalanService->updatePangkalan($id, $request->validated());

      if (!$pangkalan) {
        return response()->json([
          'success' => false,
          'message' => 'Pangkalan record not found',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Pangkalan record updated successfully',
        'data' => $pangkalan->toArray()
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
        'message' => 'Failed to update pangkalan record',
        'data' => null
      ], 500);
    }
  }

  public function destroy(string $id): JsonResponse
  {
    try {
      $deleted = $this->pangkalanService->deletePangkalan($id);

      if (!$deleted) {
        return response()->json([
          'success' => false,
          'message' => 'Pangkalan record not found',
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Pangkalan record deleted successfully',
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to delete pangkalan record',
      ], 500);
    }
  }
}
