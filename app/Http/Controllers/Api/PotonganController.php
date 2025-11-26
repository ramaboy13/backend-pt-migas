<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PotonganRequest;
use App\Services\PotonganService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PotonganController extends Controller
{
  public function __construct(private PotonganService $service) {}

  public function index(Request $request): JsonResponse
  {
    try {
      $perPage = $request->input('per_page', 10);
      $filters = $request->only(['periode', 'start_periode', 'end_periode', 'karyawan_id', 'nama_karyawan']);

      $result = $this->service->getAllPotongan($filters, $perPage);
      $responseData = $result->toArray();

      return response()->json([
        'success' => true,
        'data' => $responseData['data'],
        'meta' => $responseData['meta']
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengambil data potongan',
        'data' => null
      ], 500);
    }
  }

  public function show(string $id): JsonResponse
  {
    try {
      $potongan = $this->service->getPotonganById($id);

      if (!$potongan) {
        return response()->json([
          'success' => false,
          'message' => 'Data potongan tidak ditemukan',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'data' => $potongan
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengambil data potongan',
        'data' => null
      ], 500);
    }
  }

  public function store(PotonganRequest $request): JsonResponse
  {
    try {
      $validated = $request->validated();
      $potongan = $this->service->createPotongan($validated);

      return response()->json([
        'success' => true,
        'message' => 'Data potongan berhasil dibuat',
        'data' => $potongan
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
        'message' => 'Gagal membuat data potongan',
        'data' => null
      ], 500);
    }
  }

  public function update(PotonganRequest $request, string $id): JsonResponse
  {
    try {
      $validated = $request->validated();
      $potongan = $this->service->updatePotongan($id, $validated);

      if (!$potongan) {
        return response()->json([
          'success' => false,
          'message' => 'Data potongan tidak ditemukan',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Data potongan berhasil diupdate',
        'data' => $potongan
      ], 200);
    } catch (\InvalidArgumentException $e) {
      return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
      ], 422);
    } catch (\Exception $e) {
      Log::error('Failed to Update potongan: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString(),
        'request_data' => $request->all(),
      ]);
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengupdate data potongan',
        'data' => null
      ], 500);
    }
  }

  public function destroy(string $id): JsonResponse
  {
    try {
      $deleted = $this->service->deletePotongan($id);

      if (!$deleted) {
        return response()->json([
          'success' => false,
          'message' => 'Data potongan tidak ditemukan',
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Data potongan berhasil dihapus',
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal menghapus data potongan',
      ], 500);
    }
  }

  public function getByKaryawan(Request $request, string $karyawanId): JsonResponse
  {
    try {
      $filters = $request->only(['start_periode', 'end_periode']);
      $perPage = $request->input('per_page', 10);

      $result = $this->service->getPotonganByKaryawan($karyawanId, array_merge($filters, ['per_page' => $perPage]));
      $responseData = $result->toArray();

      return response()->json([
        'success' => true,
        'data' => $responseData['data'],
        'meta' => $responseData['meta']
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengambil data potongan karyawan',
        'data' => null
      ], 500);
    }
  }



  public function recalculate(Request $request): JsonResponse
  {
    try {
      $periode = $request->input('periode');
      $karyawanId = $request->input('karyawan_id');

      if ($periode) {
        $this->service->recalculatePotongan($periode);
        $message = 'Data potongan berhasil dihitung ulang untuk periode ' . $periode;
      } elseif ($karyawanId) {
        $this->service->recalculatePotonganByKaryawan($karyawanId);
        $message = 'Data potongan berhasil dihitung ulang untuk karyawan';
      } else {
        return response()->json([
          'success' => false,
          'message' => 'Periode atau karyawan_id harus diisi',
          'data' => null
        ], 422);
      }

      return response()->json([
        'success' => true,
        'message' => $message,
        'data' => null
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal menghitung ulang data potongan',
        'data' => null
      ], 500);
    }
  }
}
