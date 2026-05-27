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
     * Generate laporan slip gaji semua karyawan berdasarkan filter
     */
    public function generateGajiKaryawanPdf(array $filter = []): string
    {
        // Ambil data gaji dengan relasi karyawan
        $gaji = $this->gajiKaryawanRepository->getAllForPdf($filter);

        // Hitung summary keseluruhan
        $summary = [
            'total_karyawan' => $gaji->unique('karyawan_id')->count(),
            'total_gapok' => $gaji->sum('gapok'),
            'total_lembur' => $gaji->sum('total_lembur'),
            'total_tunjangan' => $gaji->sum('total_tunjangan'),
            'total_pendapatan' => $gaji->sum('total_pendapatan'),
            'total_potongan' => $gaji->sum('total_potongan'),
            'total_pph21' => $gaji->sum('pph21'),
            'total_gaji_bersih' => $gaji->sum('gaji_bersih'),
        ];

        $periodeLabel = $this->getPeriodeLabel($filter);

        $data = [
            'gajiList' => $gaji,
            'title' => 'Laporan Slip Gaji Karyawan',
            'periodeLabel' => $periodeLabel,
            'date' => now()->format('d/m/Y H:i:s'),
            'filters' => $filter,
            'summary' => $summary,
        ];

        $pdf = Pdf::loadView('pdf.slip-gaji-laporan', $data)->setPaper('a4', 'landscape');

        return $pdf->output();
    }

    /**
     * Generate slip gaji untuk satu karyawan (berdasarkan ID karyawan)
     */
    public function generateSlipGajiByKaryawanPdf(string $karyawanId, array $filter = []): string
    {
        $filter['karyawan_id'] = $karyawanId;
        $gajiList = $this->gajiKaryawanRepository->getAllForPdf($filter);

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
            'total_potongan' => $gajiList->sum('total_potongan'),
            'total_pph21' => $gajiList->sum('pph21'),
            'total_gaji_bersih' => $gajiList->sum('gaji_bersih'),
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

    public function savePdfToStorage(string $pdfContent, string $filename): string
    {
        $directory = 'pdf-slip-gaji-karyawan';
        $path = $directory.'/'.$filename;

        if (! Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $saved = Storage::disk('public')->put($path, $pdfContent);

        if (! $saved) {
            throw new \Exception('Gagal menyimpan file PDF ke storage');
        }

        return $path;
    }

    public function generateFilename(string $prefix = 'slip-gaji'): string
    {
        return $prefix.'-'.now()->format('YmdHis').'.pdf';
    }

    private function getPeriodeLabel(array $filter): string
    {
        if (! empty($filter['bulan']) && ! empty($filter['tahun'])) {
            return Carbon::create($filter['tahun'], $filter['bulan'], 1)->translatedFormat('F Y');
        }

        return 'Semua Periode';
    }
}
