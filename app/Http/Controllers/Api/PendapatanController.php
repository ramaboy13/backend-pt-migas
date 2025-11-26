<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PendapatanRequest;
use App\Services\PendapatanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PendapatanController extends Controller
{
  public function __construct(private PendapatanService $service) {}

  public function index(Request $request): JsonResponse
  {
    try {
      $perPage = $request->input('per_page', 10);
      $filters = $request->only(['periode', 'start_periode', 'end_periode', 'karyawan_id', 'nama_karyawan']);

      $result = $this->service->getAllPendapatan($filters, $perPage);
      $responseData = $result->toArray();

      return response()->json([
        'success' => true,
        'data' => $responseData['data'],
        'meta' => $responseData['meta']
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengambil data pendapatan',
        'data' => null
      ], 500);
    }
  }

  public function show(string $id): JsonResponse
  {
    try {
      $pendapatan = $this->service->getPendapatanById($id);

      if (!$pendapatan) {
        return response()->json([
          'success' => false,
          'message' => 'Data pendapatan tidak ditemukan',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'data' => $pendapatan->toArray()
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengambil data pendapatan',
        'data' => null
      ], 500);
    }
  }

  public function store(PendapatanRequest $request): JsonResponse
  {
    try {
      $validated = $request->validated();
      $pendapatan = $this->service->createPendapatan($validated);

      return response()->json([
        'success' => true,
        'message' => 'Data pendapatan berhasil dibuat',
        'data' => $pendapatan->toArray()
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
        'message' => 'Gagal membuat data pendapatan',
        'data' => null
      ], 500);
    }
  }

  public function update(PendapatanRequest $request, string $id): JsonResponse
  {
    try {
      $validated = $request->validated();
      $pendapatan = $this->service->updatePendapatan($id, $validated);

      if (!$pendapatan) {
        return response()->json([
          'success' => false,
          'message' => 'Data pendapatan tidak ditemukan',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Data pendapatan berhasil diupdate',
        'data' => $pendapatan->toArray()
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
        'message' => 'Gagal mengupdate data pendapatan',
        'data' => null
      ], 500);
    }
  }

  public function destroy(string $id): JsonResponse
  {
    try {
      $deleted = $this->service->deletePendapatan($id);

      if (!$deleted) {
        return response()->json([
          'success' => false,
          'message' => 'Data pendapatan tidak ditemukan',
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Data pendapatan berhasil dihapus',
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal menghapus data pendapatan',
      ], 500);
    }
  }

  public function getByKaryawan(Request $request, string $karyawanId): JsonResponse
  {
    try {
      $filters = $request->only(['start_periode', 'end_periode']);
      $perPage = $request->input('per_page', 10);

      $result = $this->service->getPendapatanByKaryawan($karyawanId, array_merge($filters, ['per_page' => $perPage]));
      $responseData = $result->toArray();

      return response()->json([
        'success' => true,
        'data' => $responseData['data'],
        'meta' => $responseData['meta']
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengambil data pendapatan karyawan',
        'data' => null
      ], 500);
    }
  }

  public function recalculate(Request $request): JsonResponse
  {
    try {
      $periode = $request->input('periode');

      if (!$periode) {
        return response()->json([
          'success' => false,
          'message' => 'Periode harus diisi',
          'data' => null
        ], 422);
      }

      $this->service->recalculatePendapatan($periode);

      return response()->json([
        'success' => true,
        'message' => 'Data pendapatan berhasil dihitung ulang',
        'data' => null
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal menghitung ulang data pendapatan',
        'data' => null
      ], 500);
    }
  }
}
