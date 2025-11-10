<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransaksiOperasionalRequest;
use App\Services\TransaksiOperasionalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransaksiOperasionalController extends Controller
{
  public function __construct(private TransaksiOperasionalService $service) {}

  public function index(Request $request): JsonResponse
  {
    try {
      $perPage = $request->input('per_page', 10);
      $filters = $request->only([
        'tanggal',
        'start_date',
        'end_date',
        'pangkalan_id',
        'tabung_id',
        'is_in'
      ]);

      $result = $this->service->getAllTransaksi($filters, $perPage);

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
        'message' => 'Gagal mengambil data transaksi',
        'data' => null
      ], 500);
    }
  }

  public function show(string $id): JsonResponse
  {
    try {
      $transaksi = $this->service->getTransaksiById($id);

      if (!$transaksi) {
        return response()->json([
          'success' => false,
          'message' => 'Data transaksi tidak ditemukan',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'data' => $transaksi
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengambil data transaksi',
        'data' => null
      ], 500);
    }
  }

  public function store(TransaksiOperasionalRequest $request): JsonResponse
  {
    try {
      $validated = $request->validated();
      $transaksi = $this->service->createTransaksi($validated);

      return response()->json([
        'success' => true,
        'message' => 'Transaksi berhasil dibuat',
        'data' => $transaksi
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
        'message' => 'Gagal membuat transaksi',
        'data' => null
      ], 500);
    }
  }

  public function update(TransaksiOperasionalRequest $request, string $id): JsonResponse
  {
    try {
      $validated = $request->validated();
      $transaksi = $this->service->updateTransaksi($id, $validated);

      if (!$transaksi) {
        return response()->json([
          'success' => false,
          'message' => 'Data transaksi tidak ditemukan',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Transaksi berhasil diupdate',
        'data' => $transaksi
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
        'message' => 'Gagal mengupdate transaksi',
        'data' => null
      ], 500);
    }
  }

  public function destroy(string $id): JsonResponse
  {
    try {
      $deleted = $this->service->deleteTransaksi($id);

      if (!$deleted) {
        return response()->json([
          'success' => false,
          'message' => 'Data transaksi tidak ditemukan',
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Transaksi berhasil dihapus',
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal menghapus transaksi',
      ], 500);
    }
  }

  public function getSummary(Request $request): JsonResponse
  {
    try {
      $startDate = $request->input('start_date', date('Y-m-01')); // Default awal bulan
      $endDate = $request->input('end_date', date('Y-m-d')); // Default hari ini

      $summary = $this->service->getSummaryByPeriode($startDate, $endDate);

      return response()->json([
        'success' => true,
        'data' => $summary
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengambil summary transaksi',
        'data' => null
      ], 500);
    }
  }
}
