<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GajiKaryawanRequest;
use App\Services\GajiKaryawanService;
use App\Services\pdf\PdfServiceGajiKaryawan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GajiKaryawanController extends Controller
{
    public function __construct(private GajiKaryawanService $service) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $withRelations = $request->boolean('with_relations', true);

            $filters = $request->only([
                'status', 'karyawan_aktif', 'search', 'start_date', 'end_date',
            ]);

            // Ubah format periode jika ada
            if ($request->has('periode')) {
                $periode = explode('-', $request->periode);
                if (count($periode) == 2) {
                    $filters['bulan'] = (int) $periode[1];
                    $filters['tahun'] = (int) $periode[0];
                }
            }

            $result = $this->service->getAllGaji($filters, $perPage, $withRelations);
            $responseData = $result->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Data gaji karyawan berhasil diambil',
                'data' => $responseData['data'],
                'meta' => $responseData['meta'],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving gaji karyawan: '.$e->getMessage());

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
            $withRelations = request()->boolean('with_relations', true);
            $gaji = $this->service->getGajiById($id, $withRelations);

            if (! $gaji) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data gaji karyawan tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data gaji karyawan berhasil diambil',
                'data' => $gaji->toArray(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving gaji karyawan: '.$e->getMessage());

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
            $gaji = $this->service->createGaji($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data gaji karyawan berhasil dibuat',
                'data' => $gaji->toArray(),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating gaji karyawan: '.$e->getMessage());

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
            $gaji = $this->service->updateGaji($id, $validated);

            if (! $gaji) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data gaji karyawan tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data gaji karyawan berhasil diupdate',
                'data' => $gaji->toArray(),
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating gaji karyawan: '.$e->getMessage());

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
            Log::error('Error deleting gaji karyawan: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data gaji karyawan',
            ], 500);
        }
    }

    public function getByKaryawan(Request $request, string $karyawanId): JsonResponse
    {
        try {
            $filters = $request->only(['start_date', 'end_date', 'tahun']);
            $perPage = $request->input('per_page', 10);
            $filters['per_page'] = $perPage;

            $result = $this->service->getGajiByKaryawan($karyawanId, $filters);
            $responseData = $result->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Data gaji karyawan berhasil diambil',
                'data' => $responseData['data'],
                'meta' => $responseData['meta'],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving gaji by karyawan: '.$e->getMessage());

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
            $bulan = $request->input('bulan', date('n'));
            $tahun = $request->input('tahun', date('Y'));

            $summary = $this->service->getSummaryByPeriode((int) $bulan, (int) $tahun);

            return response()->json([
                'success' => true,
                'message' => 'Summary gaji berhasil diambil',
                'data' => array_merge(['bulan' => $bulan, 'tahun' => $tahun], $summary),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving summary gaji: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil summary gaji',
                'data' => null,
            ], 500);
        }
    }

    public function generateMassGaji(Request $request): JsonResponse
    {
        try {
            $bulan = $request->input('bulan', date('n'));
            $tahun = $request->input('tahun', date('Y'));
            $potonganLainnya = $request->input('potongan_lainnya', 0);
            $pph21 = $request->input('pph21', 0);

            $result = $this->service->generateGajiForAllKaryawan(
                (int) $bulan,
                (int) $tahun,
                (float) $potonganLainnya,
                (float) $pph21
            );

            return response()->json([
                'success' => true,
                'message' => "Berhasil memproses gaji untuk {$result['success_count']} karyawan",
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error generating mass gaji: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses gaji massal',
                'data' => null,
            ], 500);
        }
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        try {
            $status = $request->input('status');

            if (! in_array($status, ['DRAFT', 'PROCESSED', 'PAID', 'APPROVED'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status tidak valid',
                    'data' => null,
                ], 422);
            }

            $gaji = $this->service->updateStatus($id, $status);

            if (! $gaji) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data gaji karyawan tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status gaji berhasil diupdate',
                'data' => $gaji->toArray(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating gaji status: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status gaji',
                'data' => null,
            ], 500);
        }
    }

    // PDF Methods (dibiarkan seperti sebelumnya)
    public function generatePdfReport(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['bulan', 'tahun', 'karyawan_id', 'status']);
            $pdfService = app(PdfServiceGajiKaryawan::class);
            $pdfContent = $pdfService->generateSlipGajiPdf($filters);
            $filename = $pdfService->generateFilename('slip-gaji-karyawan');
            $filePath = $pdfService->savePdfToStorage($pdfContent, $filename);

            return response()->json([
                'success' => true,
                'message' => 'Laporan PDF gaji karyawan berhasil dibuat',
                'data' => [
                    'url' => Storage::url($filePath),
                    'file_path' => $filePath,
                    'filename' => $filename,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error generating PDF gaji karyawan report: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat laporan PDF gaji karyawan',
                'data' => null,
            ], 500);
        }
    }

    public function downloadPdfGajiKaryawanReport(string $filename)
    {
        try {
            $path = 'pdf-slip-gaji-karyawan/'.$filename;

            if (! Storage::disk('public')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File PDF tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return Storage::download($path, $filename, [
                'Content-Type' => Storage::mimeType($path),
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        } catch (\Exception $e) {
            Log::error('Error downloading PDF gaji karyawan report: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunduh laporan PDF gaji karyawan',
                'data' => null,
            ], 500);
        }
    }
}
