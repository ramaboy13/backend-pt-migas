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
        'message' => 'Data tabung berhasil diambil',
        'data' => $responseData['data'],
        'meta' => $responseData['meta']
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengambil data tabung',
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
          'message' => 'Data tabung tidak ditemukan',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Data tabung berhasil diambil',
        'data' => $tabung->toArray()
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengambil data tabung',
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
        'message' => 'Data tabung berhasil ditambahkan',
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
        'message' => 'Gagal menambahkan data tabung',
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
          'message' => 'Data tabung tidak ditemukan',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Data tabung berhasil diperbarui',
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
        'message' => 'Gagal memperbarui data tabung',
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
          'message' => 'Data tabung tidak ditemukan',
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Data tabung berhasil dihapus',
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal menghapus data tabung',
      ], 500);
    }
  }
}
