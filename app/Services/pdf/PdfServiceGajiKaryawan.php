<?php
namespace App\Services\pdf;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Repositories\GajiKaryawanRepository;
use Illuminate\Support\Facades\Storage;


class PdfServiceGajiKaryawan
{
    public function __construct(
        private GajiKaryawanRepository $gajiKaryawanRepository
    ) {}

public function generateSlipGajiPdf(array $filter = []): string
{
    $gaji = $this->gajiKaryawanRepository->getAllForPdf($filter);

    // Group by karyawan untuk tampilan per-karyawan
    $gajiGrouped = $gaji->groupBy('karyawan_id');

    $summary = [
        'total_subtotal'    => $gaji->sum('subtotal'),
        'total_pph21'       => $gaji->sum('pph21'),
        'total_gaji_bersih' => $gaji->sum('gaji_bersih'),
        'total_karyawan'    => $gajiGrouped->count(), // unik per karyawan
        'total_entri'       => $gaji->count(),
    ];

    // Tentukan label periode untuk header
    $periodeLabel = 'Semua Periode';
    if (!empty($filter['periode'])) {
        $periodeLabel = \Carbon\Carbon::parse($filter['periode'])->translatedFormat('F Y');
    } elseif (!empty($filter['start_periode']) && !empty($filter['end_periode'])) {
        $periodeLabel = \Carbon\Carbon::parse($filter['start_periode'])->translatedFormat('F Y')
            . ' s/d '
            . \Carbon\Carbon::parse($filter['end_periode'])->translatedFormat('F Y');
    }

    $data = [
        'gaji'         => $gaji,
        'gajiGrouped'  => $gajiGrouped,
        'title'        => 'Laporan Slip Gaji Karyawan',
        'periodeLabel' => $periodeLabel,
        'date'         => now()->format('d/m/Y H:i:s'),
        'filters'      => $filter,
        'summary'      => $summary,
        'isPerKaryawan'=> !empty($filter['karyawan_id']),
    ];

    $pdf = Pdf::loadView('pdf.slip-gaji-laporan', $data)->setPaper('a4', 'landscape');

    return $pdf->output();
}

    public function generateSlipGajiByKaryawanPdf(string $karyawanId, array $filter = []): string
    {
        $gaji = $this->gajiKaryawanRepository->getByKaryawanId($karyawanId, array_merge($filter, ['per_page' => 1000]));

        if ($gaji->isEmpty()) {
            throw new \InvalidArgumentException('Tidak ada data gaji untuk karyawan ini');
        }

        $karyawan = $gaji->first()->karyawan;

        $summary = [
            'total_gaji' => $gaji->sum('total_gaji'),
            'total_potongan' => $gaji->sum('total_potongan'),
            'total_terima' => $gaji->sum('total_terima'),
            'total_periode' => $gaji->count(),
        ];

        $data = [
            'gaji' => $gaji,
            'title' => "Slip Gaji Karyawan - {$karyawan->nama}",
            'date' => now()->format('d/m/Y H:i:s'),
            'filters' => $filter,
            'summary' => $summary,
        ];

        $pdf = Pdf::loadView('pdf.slip-gaji-karyawan', $data);

        return $pdf->output();
    }

public function savePdfToStorage(string $pdfContent, string $filename): string
{
    $directory = 'pdf-slip-gaji-karyawan';
    $path = $directory . '/' . $filename;


    if (!Storage::exists($directory)) {
        Storage::makeDirectory($directory);
    }

    Storage::put($path, $pdfContent);

    return $path;
}
    public function generateFilename(string $prefix): string
    {
        return $prefix . '-' . now()->format('YmdHis') . '.pdf';    
    }
}