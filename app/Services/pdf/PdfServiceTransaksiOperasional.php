<?php

namespace App\Services\pdf;

use App\Repositories\TransaksiOperasionalRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfServiceTransaksiOperasional
{
    public function __construct(
        private TransaksiOperasionalRepository $transaksiRepository
    ) {}

    /**
     * Generate PDF untuk multiple transaksi (laporan)
     */
    public function generateLaporanTransaksiPdf(array $filters = []): string
    {
        $result = $this->transaksiRepository->getAll($filters, 1000);
        $transactions = ($result instanceof \Illuminate\Pagination\LengthAwarePaginator)
            ? $result->getCollection()
            : collect($result);

        $summary = [
            'total_transactions' => $transactions->count(),
            'total_debit' => $transactions->where('is_pemasukan', true)->sum('jumlah'),
            'total_credit' => $transactions->where('is_pemasukan', false)->sum('jumlah'),
            'net_balance' => $transactions->where('is_pemasukan', true)->sum('jumlah')
                            - $transactions->where('is_pemasukan', false)->sum('jumlah'),
        ];

        $data = [
            'transactions' => $transactions,
            'title' => 'Laporan Transaksi Operasional',
            'date' => now()->format('d/m/Y H:i:s'),
            'filters' => $filters,
            'summary' => $summary,
        ];

        $pdf = Pdf::loadView('pdf.transaksi-laporan', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->output();
    }

    /**
     * Generate PDF untuk transaksi berdasarkan pangkalan
     */
    public function generatePangkalanTransaksiPdf(string $pangkalanId, array $filters = []): string
    {
        $transactions = $this->transaksiRepository->getByPangkalan($pangkalanId, array_merge($filters, ['per_page' => 1000]));

        if ($transactions->isEmpty()) {
            throw new \InvalidArgumentException('Tidak ada transaksi untuk pangkalan ini');
        }

        $pangkalan = $transactions->first()->pangkalan;

        $summary = [
            'total_debit' => $transactions->sum('debit'),
            'total_credit' => $transactions->sum('credit'),
            'total_transactions' => $transactions->count(),
            'net_balance' => $transactions->sum('debit') - $transactions->sum('credit'),
        ];

        $data = [
            'transactions' => $transactions,
            'pangkalan' => $pangkalan,
            'title' => 'Laporan Transaksi Pangkalan',
            'date' => now()->format('d/m/Y H:i:s'),
            'filters' => $filters,
            'summary' => $summary,
        ];

        $pdf = Pdf::loadView('pdf.transaksi-pangkalan', $data);

        return $pdf->output();
    }

    /**
     * Generate PDF untuk transaksi berdasarkan periode
     */
    public function generatePeriodeTransaksiPdf(string $startDate, string $endDate): string
    {
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $transactions = $this->transaksiRepository->getAll($filters, 1000);

        $summary = [
            'total_debit' => $transactions->sum('debit'),
            'total_credit' => $transactions->sum('credit'),
            'total_transactions' => $transactions->count(),
            'net_balance' => $transactions->sum('debit') - $transactions->sum('credit'),
        ];

        $data = [
            'transactions' => $transactions,
            'title' => 'Laporan Transaksi Periode',
            'date' => now()->format('d/m/Y H:i:s'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'summary' => $summary,
        ];

        $pdf = Pdf::loadView('pdf.transaksi-periode', $data);

        return $pdf->output();
    }

    /**
     * Save PDF to storage and return file path
     */
    public function savePdfToStorage(string $pdfContent, string $filename): string
    {
        $path = 'pdf-reports/'.$filename;
        Storage::put($path, $pdfContent);

        return $path;
    }

    /**
     * Generate unique filename
     */
    public function generateFilename(string $prefix = 'transaksi-operasional'): string
    {
        return $prefix.'_'.now()->format('Ymd_His').'.pdf';
    }
}
