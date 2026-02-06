<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GajiKaryawanRequest;
use App\Services\GajiKaryawanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GajiKaryawanController extends Controller
{
    public function __construct(private GajiKaryawanService $service) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $filters = $request->only(['periode', 'start_periode', 'end_periode', 'karyawan_aktif', 'search']);

            $result = $this->service->getAllGaji($filters, $perPage);
            $responseData = $result->toArray();

            return response()->json([
                'success' => true,
                'data' => $responseData['data'],
                'meta' => $responseData['meta'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data gaji karyawan',
                'data' => null,
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $gajiKaryawan = $this->service->getGajiById($id);

            if (! $gajiKaryawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data gaji karyawan tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $gajiKaryawan->toArray(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data gaji karyawan',
                'data' => null,
            ], 500);
        }
    }

    public function store(GajiKaryawanRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $gajiKaryawan = $this->service->createGaji($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data gaji karyawan berhasil dibuat',
                'data' => $gajiKaryawan->toArray(),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat data gaji karyawan',
                'data' => null,
            ], 500);
        }
    }

    public function update(GajiKaryawanRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $gajiKaryawan = $this->service->updateGaji($id, $validated);

            if (! $gajiKaryawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data gaji karyawan tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data gaji karyawan berhasil diupdate',
                'data' => $gajiKaryawan->toArray(),
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data gaji karyawan',
                'data' => null,
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->service->deleteGaji($id);

            if (! $deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data gaji karyawan tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data gaji karyawan berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data gaji karyawan',
            ], 500);
        }
    }

    public function getByKaryawan(Request $request, string $karyawanId): JsonResponse
    {
        try {
            $filters = $request->only(['start_periode', 'end_periode']);
            $perPage = $request->input('per_page', 10);

            $result = $this->service->getGajiByKaryawan($karyawanId, array_merge($filters, ['per_page' => $perPage]));
            $responseData = $result->toArray();

            return response()->json([
                'success' => true,
                'data' => $responseData['data'],
                'meta' => $responseData['meta'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data gaji karyawan',
                'data' => null,
            ], 500);
        }
    }

    public function getSummaryByPeriode(Request $request): JsonResponse
    {
        try {
            $periode = $request->input('periode', date('Y-m-d'));
            $summary = $this->service->getSummaryByPeriode($periode);

            return response()->json([
                'success' => true,
                'data' => array_merge(['periode' => $periode], $summary),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil summary gaji',
                'data' => null,
            ], 500);
        }
    }

    public function recalculate(Request $request): JsonResponse
    {
        try {
            $periode = $request->input('periode');
            $pendapatanId = $request->input('pendapatan_id');
            $potonganId = $request->input('potongan_id');

            if ($periode) {
                $this->service->recalculateGaji($periode);
                $message = 'Data gaji berhasil dihitung ulang untuk periode '.$periode;
            } elseif ($pendapatanId) {
                $this->service->recalculateGajiByPendapatan($pendapatanId);
                $message = 'Data gaji berhasil dihitung ulang untuk pendapatan';
            } elseif ($potonganId) {
                $this->service->recalculateGajiByPotongan($potonganId);
                $message = 'Data gaji berhasil dihitung ulang untuk potongan';
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode, pendapatan_id, atau potongan_id harus diisi',
                    'data' => null,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung ulang data gaji',
                'data' => null,
            ], 500);
        }
    }
}
