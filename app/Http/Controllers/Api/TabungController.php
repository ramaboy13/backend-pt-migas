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
      $perPage = $request->get('per_page', 15);
      $tabungs = $this->tabungService->getAllTabung($perPage);

      return response()->json([
        'success' => true,
        'message' => 'Tabung retrieved successfully',
        'data' => $tabungs->items(),
        'meta' => [
          'current_page' => $tabungs->currentPage(),
          'per_page' => $tabungs->perPage(),
          'total' => $tabungs->total(),
          'last_page' => $tabungs->lastPage(),
        ]
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve tabung',
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
          'message' => 'Tabung not found',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Tabung retrieved successfully',
        'data' => $tabung
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve tabung',
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
        'message' => 'Tabung successfully created',
        'data' => $tabung
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
        'message' => 'Failed to create tabung',
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
          'message' => 'Tabung not found',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Tabung successfully updated',
        'data' => $tabung
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
        'message' => 'Failed to update tabung',
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
          'message' => 'Tabung not found',
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Tabung successfully deleted',
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to delete tabung',
      ], 500);
    }
  }
}
