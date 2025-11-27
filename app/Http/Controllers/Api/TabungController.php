<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TabungRequest;
use App\Services\TabungService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TabungController extends Controller
{
  public function __construct(private TabungService $tabungService) {}

  public function index(Request $request): JsonResponse
  {
    try {
      $perPage = $request->get('per_page', 10);

      $result = $this->tabungService->getAllTabung($perPage);
      $responseData = $result->toArray();

      return response()->json([
        'success' => true,
        'message' => 'Tabung records retrieved successfully',
        'data' => $responseData['data'],
        'meta' => $responseData['meta']
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve tabung records',
        'data' => null
      ], 500);
    }
  }

  public function show(string $id): JsonResponse
  {
    try {
      $tabung = $this->tabungService->getTabungById($id);

      if (!$tabung) {
        return response()->json([
          'success' => false,
          'message' => 'Tabung record not found',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Tabung record retrieved successfully',
        'data' => $tabung->toArray()
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve tabung record',
        'data' => null
      ], 500);
    }
  }

  public function store(TabungRequest $request): JsonResponse
  {
    try {
      $tabung = $this->tabungService->createTabung($request->validated());

      return response()->json([
        'success' => true,
        'message' => 'Tabung record created successfully',
        'data' => $tabung->toArray()
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
        'message' => 'Failed to create tabung record',
        'data' => null
      ], 500);
    }
  }

  public function update(TabungRequest $request, string $id): JsonResponse
  {
    try {
      $tabung = $this->tabungService->updateTabung($id, $request->validated());

      if (!$tabung) {
        return response()->json([
          'success' => false,
          'message' => 'Tabung record not found',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Tabung record updated successfully',
        'data' => $tabung->toArray()
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
        'message' => 'Failed to update tabung record',
        'data' => null
      ], 500);
    }
  }

  public function destroy(string $id): JsonResponse
  {
    try {
      $deleted = $this->tabungService->deleteTabung($id);

      if (!$deleted) {
        return response()->json([
          'success' => false,
          'message' => 'Tabung record not found',
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Tabung record deleted successfully',
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to delete tabung record',
      ], 500);
    }
  }
}
