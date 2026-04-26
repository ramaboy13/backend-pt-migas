<?php

namespace App\Services\pdf;

use App\Repositories\GajiKaryawanRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PdfServiceGajiKaryawan
{
    public function __construct(
        private GajiKaryawanRepository $gajiKaryawanRepository
    ) {}

    /**
     * Generate laporan slip gaji (multiple karyawan)
     */
    public function generateSlipGajiPdf(array $filter = []): string
    {
        $gajiList = $this->gajiKaryawanRepository->getAllForPdf($filter);

        // Group by karyawan untuk tampilan per-karyawan
        $gajiGrouped = $gajiList->groupBy('karyawan_id');

        // Summary keseluruhan
        $summary = [
            'total_karyawan' => $gajiGrouped->count(),
            'total_entri' => $gajiList->count(),
            'total_gapok' => $gajiList->sum('gapok'),
            'total_lembur' => $gajiList->sum('total_lembur'),
            'total_tunjangan' => $gajiList->sum('total_tunjangan'),
            'total_pendapatan' => $gajiList->sum('total_pendapatan'),
            'total_potongan_bpjs_kesehatan' => $gajiList->sum('potongan_bpjs_kesehatan'),
            'total_potongan_bpjs_tenagakerja' => $gajiList->sum('potongan_bpjs_tenagakerja'),
            'total_potongan_lainnya' => $gajiList->sum('potongan_lainnya'),
            'total_potongan' => $gajiList->sum('total_potongan'),
            'total_pph21' => $gajiList->sum('pph21'),
            'total_gaji_bersih' => $gajiList->sum('gaji_bersih'),
        ];

        // Tentukan label periode untuk header
        $periodeLabel = $this->getPeriodeLabel($filter);

        $data = [
            'gajiList' => $gajiList,
            'gajiGrouped' => $gajiGrouped,
            'title' => 'Laporan Slip Gaji Karyawan',
            'periodeLabel' => $periodeLabel,
            'date' => now()->format('d/m/Y H:i:s'),
            'filters' => $filter,
            'summary' => $summary,
            'isPerKaryawan' => ! empty($filter['karyawan_id']),
        ];

        $pdf = Pdf::loadView('pdf.slip-gaji-laporan', $data)->setPaper('a4', 'landscape');

        return $pdf->output();
    }

    /**
     * Generate slip gaji per karyawan (detail per periode)
     */
    public function generateSlipGajiByKaryawanPdf(string $karyawanId, array $filter = []): string
    {
        $gajiList = $this->gajiKaryawanRepository->getByKaryawanId($karyawanId, array_merge($filter, ['per_page' => 1000]));

        if ($gajiList->isEmpty()) {
            throw new \InvalidArgumentException('Tidak ada data gaji untuk karyawan ini');
        }

        $karyawan = $gajiList->first()->karyawan;

        // Summary per karyawan
        $summary = [
            'total_periode' => $gajiList->count(),
            'total_gapok' => $gajiList->sum('gapok'),
            'total_lembur' => $gajiList->sum('total_lembur'),
            'total_tunjangan' => $gajiList->sum('total_tunjangan'),
            'total_pendapatan' => $gajiList->sum('total_pendapatan'),
            'total_potongan_bpjs_kesehatan' => $gajiList->sum('potongan_bpjs_kesehatan'),
            'total_potongan_bpjs_tenagakerja' => $gajiList->sum('potongan_bpjs_tenagakerja'),
            'total_potongan_lainnya' => $gajiList->sum('potongan_lainnya'),
            'total_potongan' => $gajiList->sum('total_potongan'),
            'total_pph21' => $gajiList->sum('pph21'),
            'total_gaji_bersih' => $gajiList->sum('gaji_bersih'),
            'rata_rata_gaji' => $gajiList->avg('gaji_bersih'),
        ];

        $data = [
            'gajiList' => $gajiList,
            'karyawan' => $karyawan,
            'title' => "Slip Gaji Karyawan - {$karyawan->nama}",
            'date' => now()->format('d/m/Y H:i:s'),
            'filters' => $filter,
            'summary' => $summary,
        ];

        $pdf = Pdf::loadView('pdf.slip-gaji-karyawan', $data)->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * Generate laporan rekap gaji per periode (summary)
     */
    public function generateRekapGajiPeriodePdf(int $bulan, int $tahun): string
    {
        $gajiList = $this->gajiKaryawanRepository->getAll([
            'bulan' => $bulan,
            'tahun' => $tahun,
        ], 1000, true);

        if ($gajiList->isEmpty()) {
            throw new \InvalidArgumentException("Tidak ada data gaji untuk periode {$bulan}/{$tahun}");
        }

        // Summary per periode
        $summary = [
            'total_karyawan' => $gajiList->count(),
            'total_gapok' => $gajiList->sum('gapok'),
            'total_lembur' => $gajiList->sum('total_lembur'),
            'total_tunjangan' => $gajiList->sum('total_tunjangan'),
            'total_pendapatan' => $gajiList->sum('total_pendapatan'),
            'total_potongan_bpjs_kesehatan' => $gajiList->sum('potongan_bpjs_kesehatan'),
            'total_potongan_bpjs_tenagakerja' => $gajiList->sum('potongan_bpjs_tenagakerja'),
            'total_potongan_lainnya' => $gajiList->sum('potongan_lainnya'),
            'total_potongan' => $gajiList->sum('total_potongan'),
            'total_pph21' => $gajiList->sum('pph21'),
            'total_gaji_bersih' => $gajiList->sum('gaji_bersih'),
            'tertinggi' => $gajiList->max('gaji_bersih'),
            'terendah' => $gajiList->min('gaji_bersih'),
            'rata_rata' => $gajiList->avg('gaji_bersih'),
        ];

        $namaBulan = Carbon::create($tahun, $bulan, 1)->translatedFormat('F');
        $periodeLabel = "{$namaBulan} {$tahun}";

        $data = [
            'gajiList' => $gajiList,
            'title' => "Rekap Gaji Karyawan - {$periodeLabel}",
            'periodeLabel' => $periodeLabel,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'date' => now()->format('d/m/Y H:i:s'),
            'summary' => $summary,
        ];

        $pdf = Pdf::loadView('pdf.rekap-gaji-periode', $data)->setPaper('a4', 'landscape');

        return $pdf->output();
    }

    /**
     * Generate slip gaji individual untuk satu karyawan di satu periode
     */
    public function generateSlipGajiIndividualPdf(string $gajiId): string
    {
        $gaji = $this->gajiKaryawanRepository->findById($gajiId, true);

        if (! $gaji) {
            throw new \InvalidArgumentException('Data gaji tidak ditemukan');
        }

        $karyawan = $gaji->karyawan;
        $namaBulan = Carbon::create($gaji->tahun, $gaji->bulan, 1)->translatedFormat('F');

        // Ambil detail komponen lembur dan tunjangan
        $komponenLembur = $gaji->komponenGajiLembur ?? collect();
        $komponenTunjangan = $gaji->komponenGajiTunjangan ?? collect();

        $data = [
            'gaji' => $gaji,
            'karyawan' => $karyawan,
            'komponenLembur' => $komponenLembur,
            'komponenTunjangan' => $komponenTunjangan,
            'title' => "Slip Gaji - {$karyawan->nama} - {$namaBulan} {$gaji->tahun}",
            'namaBulan' => $namaBulan,
            'date' => now()->format('d/m/Y H:i:s'),
            'statusDisplay' => $gaji->status_display,
        ];

        $pdf = Pdf::loadView('pdf.slip-gaji-individual', $data)->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * Save PDF to storage
     */
    public function savePdfToStorage(string $pdfContent, string $filename): string
    {
        $directory = 'pdf-slip-gaji-karyawan';
        $path = $directory.'/'.$filename;

        if (! Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        Storage::disk('public')->put($path, $pdfContent);

        return $path;
    }

    /**
     * Generate filename with prefix
     */
    public function generateFilename(string $prefix): string
    {
        return $prefix.'-'.now()->format('YmdHis').'.pdf';
    }

    /**
     * Get periode label from filters
     */
    private function getPeriodeLabel(array $filter): string
    {
        if (! empty($filter['bulan']) && ! empty($filter['tahun'])) {
            return Carbon::create($filter['tahun'], $filter['bulan'], 1)->translatedFormat('F Y');
        }

        if (! empty($filter['start_periode']) && ! empty($filter['end_periode'])) {
            $start = Carbon::parse($filter['start_periode']);
            $end = Carbon::parse($filter['end_periode']);

            if ($start->format('Y') === $end->format('Y')) {
                return $start->translatedFormat('F').' s/d '.$end->translatedFormat('F Y');
            }

            return $start->translatedFormat('F Y').' s/d '.$end->translatedFormat('F Y');
        }

        return 'Semua Periode';
    }

    /**
     * Delete old PDF files (lebih dari 30 hari)
     */
    public function cleanupOldPdfs(int $days = 30): int
    {
        $directory = 'pdf-slip-gaji-karyawan';
        $files = Storage::disk('public')->files($directory);
        $deletedCount = 0;
        $cutoffDate = now()->subDays($days);

        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp(
                Storage::disk('public')->lastModified($file)
            );

            if ($lastModified->lt($cutoffDate)) {
                Storage::disk('public')->delete($file);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
