<?php

namespace App\Services\pdf;

use App\Repositories\KasPerusahaanRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfServiceKasPerusahaan
{
    public function __construct(
        private KasPerusahaanRepository $kasPerusahaanRepository
    ) {}

    /**
     * Generate PDF untuk multiple kas perusahaan (laporan)
     */
    public function generateLaporanKasPerusahaanPdf(array $filters = []): string
    {
        $paginator = $this->kasPerusahaanRepository->getAllPaginated($filters, 1000);
        $kasEntries = $paginator->getCollection();

        $totalMasuk = $kasEntries->filter(fn ($i) => strtoupper($i->tipe_transaksi) === 'DEBIT')->sum('jumlah');
        $totalKeluar = $kasEntries->filter(fn ($i) => strtoupper($i->tipe_transaksi) === 'KREDIT')->sum('jumlah');

        $summary = [
            'total_masuk' => $totalMasuk,
            'total_keluar' => $totalKeluar,
            'total_entries' => $kasEntries->count(),
        ];

        $data = [
            'kasEntries' => $kasEntries,
            'title' => 'Laporan Kas Perusahaan',
            'date' => now()->format('d/m/Y H:i:s'),
            'filters' => $filters,
            'summary' => $summary,
        ];

        $pdf = Pdf::loadView('pdf.kas-perusahaan-laporan', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->output();
    }

    /**
     * Save PDF to storage and return file path
     */
    public function savePdfToStorage(string $pdfContent, string $filename): string
    {
        $path = 'pdf-reports-kas-perusahaan/'.$filename;
        Storage::disk('local')->put($path, $pdfContent);

        return $path;
    }

    /**
     * Generate unique filename for PDF report
     */
    public function generateFilename(string $prefix = 'laporan-kas-perusahaan'): string
    {
        return $prefix.'-'.now()->format('Y-m-d_H-i-s').'.pdf';
    }
}
