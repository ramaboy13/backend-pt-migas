<?php

namespace App\Services;

use App\DTO\KasPerusahaan\KasPerusahaanCollectionDTO;
use App\DTO\KasPerusahaan\KasPerusahaanDTO;
use App\Models\SumberKas;
use App\Repositories\KasPerusahaanRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KasPerusahaanService
{
    public function __construct(
        private KasPerusahaanRepository $repository,
        private DashboardService $dashboardService
    ) {}

    public function getAllKasPerusahaan(
        array $filters = [],
        int $perPage = 10
    ): KasPerusahaanCollectionDTO {
        $paginator = $this->repository->getAllPaginated($filters, $perPage);

        return KasPerusahaanCollectionDTO::fromPaginator($paginator);
    }

    public function getKasPerusahaanById(string $id): ?KasPerusahaanDTO
    {
        $kasPerusahaan = $this->repository->findById($id);

        return $kasPerusahaan
            ? KasPerusahaanDTO::fromModel($kasPerusahaan)
            : null;
    }

    public function createKasPerusahaan(array $data): KasPerusahaanDTO
    {
        return DB::transaction(function () use ($data) {
            Log::info('createKasPerusahaan dipanggil dengan data: ', $data);
            $sumberKas = SumberKas::where('id', $data['sumber_kas_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (! $sumberKas->aktif) {
                throw new \InvalidArgumentException('Sumber kas tidak aktif');
            }

            if ($data['tipe_transaksi'] === 'KREDIT') {
                $this->validateSaldoCukup(
                    $sumberKas->saldo_terakhir,
                    $data['jumlah'],
                    $sumberKas->nama_display ?? 'Sumber Kas'
                );
            }

            $saldoSebelum = (float) $sumberKas->saldo_terakhir;
            $saldoSesudah = $this->calculateSaldoSesudah(
                $saldoSebelum,
                $data['tipe_transaksi'],
                $data['jumlah']
            );

            $data['saldo_sebelum'] = $saldoSebelum;
            $data['saldo_sesudah'] = $saldoSesudah;
            $data['created_by'] = Auth::user()->name ?? 'system';

            $kasPerusahaan = $this->repository->create($data);

            $sumberKas->saldo_terakhir = $saldoSesudah;
            $sumberKas->save();

            $this->dashboardService->clearCache();

            return KasPerusahaanDTO::fromModel($kasPerusahaan);
        });
    }

    public function updateKasPerusahaan(string $id, array $data): ?KasPerusahaanDTO
    {
        return DB::transaction(function () use ($id, $data) {
            $existing = $this->repository->findById($id, false);

            if (! $existing) {
                return null;
            }

            $needRecalculation =
                isset($data['sumber_kas_id']) ||
                isset($data['tipe_transaksi']) ||
                isset($data['jumlah']) ||
                isset($data['tanggal']);

            if ($needRecalculation) {
                $sumberKasId = $data['sumber_kas_id'] ?? $existing->sumber_kas_id;

                $sumberKas = SumberKas::where('id', $sumberKasId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $sumberKas->aktif) {
                    throw new \InvalidArgumentException('Sumber kas tidak aktif');
                }

                $tipeTransaksi = $data['tipe_transaksi'] ?? $existing->tipe_transaksi;
                $jumlah = $data['jumlah'] ?? $existing->jumlah;

                if ($tipeTransaksi === 'KREDIT') {
                    $existingIsKredit = $existing->tipe_transaksi === 'KREDIT';
                    $jumlahBertambah =
                        isset($data['jumlah']) && $data['jumlah'] > $existing->jumlah;

                    if (! $existingIsKredit || $jumlahBertambah) {
                        $this->validateSaldoCukup(
                            $sumberKas->saldo_terakhir,
                            $jumlah,
                            $sumberKas->nama_display ?? 'Sumber Kas'
                        );
                    }
                }

                $saldoSebelum = (float) $sumberKas->saldo_terakhir;
                $saldoSesudah = $this->calculateSaldoSesudah(
                    $saldoSebelum,
                    $tipeTransaksi,
                    $jumlah
                );

                $data['saldo_sebelum'] = $saldoSebelum;
                $data['saldo_sesudah'] = $saldoSesudah;

                $sumberKas->saldo_terakhir = $saldoSesudah;
                $sumberKas->save();
            }

            if (! $this->repository->update($id, $data)) {
                return null;
            }

            $this->dashboardService->clearCache();

            return $this->getKasPerusahaanById($id);
        });
    }

    public function deleteKasPerusahaan(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $kasToDelete = $this->repository->findById($id, false);

            if (! $kasToDelete) {
                return false;
            }

            $sumberKas = SumberKas::where('id', $kasToDelete->sumber_kas_id)
                ->lockForUpdate()
                ->first();

            if (! $sumberKas) {
                return false;
            }

            $laterRecordsQuery = $this->repository->getAllAfterDateQuery(
                $kasToDelete->sumber_kas_id,
                $kasToDelete->tanggal,
                $kasToDelete->created_at
            );

            $currentSaldo = $kasToDelete->saldo_sebelum;

            if ($this->repository->delete($id)) {
                $laterRecordsQuery->chunk(100, function ($records) use (&$currentSaldo) {
                    foreach ($records as $record) {
                        if ($record->tipe_transaksi === 'DEBIT') {
                            $record->saldo_sebelum = $currentSaldo;
                            $record->saldo_sesudah = $currentSaldo + $record->jumlah;
                        } else {
                            $record->saldo_sebelum = $currentSaldo;
                            $record->saldo_sesudah = $currentSaldo - $record->jumlah;
                        }

                        $currentSaldo = $record->saldo_sesudah;
                        $record->save();
                    }
                });

                $sumberKas->saldo_terakhir = $currentSaldo;
                $sumberKas->save();

                $this->dashboardService->clearCache();

                return true;
            }

            return false;
        });
    }

    private function validateSaldoCukup(
        float $saldoTersedia,
        float $jumlahDibutuhkan,
        string $namaSumberKas
    ): void {
        if ($saldoTersedia < $jumlahDibutuhkan) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Saldo di %s tidak cukup. Saldo tersedia: %s, Jumlah diperlukan: %s',
                    $namaSumberKas,
                    number_format($saldoTersedia, 0, ',', '.'),
                    number_format($jumlahDibutuhkan, 0, ',', '.')
                )
            );
        }
    }

    private function calculateSaldoSesudah(
        float $saldoSebelum,
        string $tipeTransaksi,
        float $jumlah
    ): float {
        return match ($tipeTransaksi) {
            'DEBIT' => $saldoSebelum + $jumlah,
            'KREDIT' => $saldoSebelum - $jumlah,
            default => throw new \InvalidArgumentException(
                'Tipe transaksi tidak valid. Harus DEBIT atau KREDIT'
            ),
        };
    }
}
