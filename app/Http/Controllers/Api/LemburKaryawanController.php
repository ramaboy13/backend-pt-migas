<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LemburKaryawanRequest;
use App\Services\LemburKaryawanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LemburKaryawanController extends Controller
{
  public function __construct(private LemburKaryawanService $service) {}

  public function index(Request $request): JsonResponse
  {
    try {
      $perPage = $request->input('per_page', 10);
      $filters = $request->only(['tanggal', 'start_date', 'end_date', 'karyawan_id', 'nama_karyawan']);

      $result = $this->service->getAllLembur($filters, $perPage);

      return response()->json([
        'success' => true,
        'data' => $result->items(),
        'meta' => [
          'current_page' => $result->currentPage(),
          'total' => $result->total(),
          'per_page' => $result->perPage(),
          'last_page' => $result->lastPage()
        ]
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve lembur',
        'data' => null
      ], 500);
    }
  }

  public function show(string $id): JsonResponse
  {
    try {
      $lembur = $this->service->getLemburById($id);

      if (!$lembur) {
        return response()->json([
          'success' => false,
          'message' => 'Data lembur not found',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'data' => $lembur
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve lembur',
        'data' => null
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
        'message' => 'Data successfully created',
        'data' => $lembur
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
        'message' => 'Failed to create lembur',
        'data' => null
      ], 500);
    }
  }

  public function update(LemburKaryawanRequest $request, string $id): JsonResponse
  {
    try {
      $validated = $request->validated();
      $lembur = $this->service->updateLembur($id, $validated);

      if (!$lembur) {
        return response()->json([
          'success' => false,
          'message' => 'Failed to retrieve lembur',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Data successfully updated',
        'data' => $lembur
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
        'message' => 'Failed to update lembur',
        'data' => null
      ], 500);
    }
  }

  public function destroy(string $id): JsonResponse
  {
    try {
      $deleted = $this->service->deleteLembur($id);

      if (!$deleted) {
        return response()->json([
          'success' => false,
          'message' => 'Failed to retrieve lembur',
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Data successfully deleted',
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to delete lembur',
      ], 500);
    }
  }

  public function getByKaryawan(Request $request, string $karyawanId): JsonResponse
  {
    try {
      $filters = $request->only(['start_date', 'end_date']);
      $perPage = $request->input('per_page', 10);

      $result = $this->service->getLemburByKaryawan($karyawanId, array_merge($filters, ['per_page' => $perPage]));

      return response()->json([
        'success' => true,
        'message' => 'Lembur records retrieved successfully',
        'data' => $result->items(),
        'meta' => [
          'current_page' => $result->currentPage(),
          'total' => $result->total(),
          'per_page' => $result->perPage(),
          'last_page' => $result->lastPage()
        ]
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve lembur',
        'data' => null
      ], 500);
    }
  }
}
