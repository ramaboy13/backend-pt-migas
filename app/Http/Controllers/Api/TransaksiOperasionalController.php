<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransaksiOperasionalRequest;
use App\Services\pdf\PdfServiceTransaksiOperasional;
use App\Services\TransaksiOperasionalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
                'is_in',
                'no_ref',
                'keterangan',
            ]);

            $result = $this->service->getAllTransaksi($filters, $perPage);
            $responseData = $result->toArray();

            return response()->json([
                'success' => true,
                'message' => 'Data transaksi berhasil diambil',
                'data' => $responseData['data'],
                'meta' => $responseData['meta'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data transaksi',
                'data' => null,
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $transaksi = $this->service->getTransaksiById($id);

            if (! $transaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data transaksi tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data transaksi berhasil diambil',
                'data' => $transaksi->toArray(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data transaksi',
                'data' => null,
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
                'data' => $transaksi->toArray(),
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
                'message' => 'Gagal membuat transaksi',
                'data' => null,
            ], 500);
        }
    }

    public function update(TransaksiOperasionalRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $transaksi = $this->service->updateTransaksi($id, $validated);

            if (! $transaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data transaksi tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diupdate',
                'data' => $transaksi->toArray(),
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
                'message' => 'Gagal mengupdate transaksi',
                'data' => null,
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->service->deleteTransaksi($id);
            if (! $deleted) {
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
            $startDate = $request->input('start_date', date('Y-m-01'));
            $endDate = $request->input('end_date', date('Y-m-d'));
            $summary = $this->service->getSummaryByPeriode($startDate, $endDate);

            return response()->json([
                'success' => true,
                'message' => 'Summary transaksi berhasil diambil',
                'data' => $summary,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil summary transaksi',
                'data' => null,
            ], 500);
        }
    }

    public function getStock(Request $request): JsonResponse
    {
        try {
            $pangkalanId = $request->input('pangkalan_id');
            $tabungId = $request->input('tabung_id');

            if (! $pangkalanId || ! $tabungId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pangkalan ID dan Tabung ID harus diisi',
                    'data' => null,
                ], 422);
            }
            $stock = $this->service->getStockSummary($pangkalanId, $tabungId);

            return response()->json([
                'success' => true,
                'data' => $stock,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data stok',
                'data' => null,
            ], 500);
        }
    }

    public function recalculate(Request $request): JsonResponse
    {
        try {
            $pangkalanId = $request->input('pangkalan_id');

            if (! $pangkalanId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pangkalan ID harus diisi',
                    'data' => null,
                ], 422);
            }

            $this->service->recalculateTransaksi($pangkalanId);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihitung ulang',
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error recalculating transactions: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung ulang transaksi',
                'data' => null,
            ], 500);
        }
    }

    public function generatePdfReport(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'tanggal', 'start_date', 'end_date', 'pangkalan_id',
                'tabung_id', 'is_in', 'no_ref', 'keterangan',
            ]);

            $pdfService = app(PdfServiceTransaksiOperasional::class);
            $pdfContent = $pdfService->generateLaporanTransaksiPdf($filters);
            $filename = $pdfService->generateFilename('laporan_transaksi');

            // Simpan ke storage
            $filePath = $pdfService->savePdfToStorage($pdfContent, $filename);

            return response()->json([
                'success' => true,
                'message' => 'Laporan PDF berhasil dibuat',
                'data' => [
                    'download_url' => Storage::url($filePath),
                    'file_path' => $filePath,
                    'filename' => $filename,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat laporan PDF',
                'data' => null,
            ], 500);
        }
    }

    public function generatePdfPangkalan(Request $request, string $pangkalanId): JsonResponse
    {
        try {
            $filters = $request->only(['start_date', 'end_date']);

            $pdfService = app(PdfServiceTransaksiOperasional::class);
            $pdfContent = $pdfService->generatePangkalanTransaksiPdf($pangkalanId, $filters);
            $filename = $pdfService->generateFilename('laporan_pangkalan_'.$pangkalanId);

            // Simpan ke storage
            $filePath = $pdfService->savePdfToStorage($pdfContent, $filename);

            return response()->json([
                'success' => true,
                'message' => 'Laporan PDF pangkalan berhasil dibuat',
                'data' => [
                    'download_url' => Storage::url($filePath),
                    'file_path' => $filePath,
                    'filename' => $filename,
                ],
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat laporan PDF pangkalan',
                'data' => null,
            ], 500);
        }
    }

    public function downloadPdfCorrect(string $filename)
    {
        try {
            $path = 'pdf-reports/'.$filename;

            // Cek apakah file ada di storage
            if (! Storage::exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan di storage',
                    'storage_path' => $path,
                    'available_files' => Storage::files('pdf-reports/'),
                ], 404);
            }

            // Download file
            return Storage::download($path, $filename, [
                'Content-Type' => Storage::mimeType($path),
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendownload: '.$e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function previewPdfCorrect(string $filename)
    {
        try {
            $path = 'pdf-reports/'.$filename;

            if (! Storage::exists($path)) {
                abort(404, 'File tidak ditemukan');
            }

            $file = Storage::get($path);
            $mimeType = Storage::mimeType($path);

            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="'.$filename.'"')
                ->header('Content-Length', Storage::size($path));

        } catch (\Exception $e) {
            abort(500, 'Gagal memuat file');
        }
    }
}
