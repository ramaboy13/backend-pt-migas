<?php

namespace App\Services;

use App\DTO\KasPerusahaan\KasPerusahaanCollectionDTO;
use App\DTO\KasPerusahaan\KasPerusahaanDTO;
use App\Repositories\KasPerusahaanRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KasPerusahaanService
{
    public function __construct(
        private KasPerusahaanRepository $repository,
        private SumberKasService $sumberKasService
    ) {}

    public function getAllKasPerusahaan(array $filters = [], int $perPage = 10): KasPerusahaanCollectionDTO
    {
        $paginator = $this->repository->getAllPaginated($filters, $perPage);

        return KasPerusahaanCollectionDTO::fromPaginator($paginator);
    }

    public function getKasPerusahaanById(string $id): ?KasPerusahaanDTO
    {
        $kasPerusahaan = $this->repository->findById($id);

        if (! $kasPerusahaan) {
            return null;
        }

        return KasPerusahaanDTO::fromModel($kasPerusahaan);
    }

    public function createKasPerusahaan(array $data): KasPerusahaanDTO
    {
        return DB::transaction(function () use ($data) {
            // Validate sumber kas exists and active
            $this->validateSumberKas($data['sumber_kas_id']);

            // Calculate saldo sebelum
            $saldoSebelum = $this->repository->getSaldoSebelum(
                $data['sumber_kas_id'],
                $data['tanggal']
            );

            // Calculate saldo sesudah
            $saldoSesudah = $this->calculateSaldoSesudah(
                $saldoSebelum,
                $data['tipe_transaksi'],
                $data['jumlah']
            );

            // Add calculated fields to data
            $data['saldo_sebelum'] = $saldoSebelum;
            $data['saldo_sesudah'] = $saldoSesudah;
            $data['created_by'] = Auth::user()->name ?? 'system';

            // Create kas perusahaan record
            $kasPerusahaan = $this->repository->create($data);

            // Update saldo terakhir in sumber_kas
            $this->repository->updateSaldoTerakhirSumberKas(
                $data['sumber_kas_id'],
                $saldoSesudah
            );

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

            // If changing sumber_kas_id or tipe_transaksi or jumlah, recalculate
            $needRecalculation = isset($data['sumber_kas_id']) ||
                                isset($data['tipe_transaksi']) ||
                                isset($data['jumlah']);

            if ($needRecalculation) {
                $sumberKasId = $data['sumber_kas_id'] ?? $existing->sumber_kas_id;
                $tanggal = $data['tanggal'] ?? $existing->tanggal->toDateString();
                $tipeTransaksi = $data['tipe_transaksi'] ?? $existing->tipe_transaksi;
                $jumlah = $data['jumlah'] ?? $existing->jumlah;

                // Validate sumber kas
                $this->validateSumberKas($sumberKasId);

                // Recalculate saldo
                $saldoSebelum = $this->repository->getSaldoSebelum($sumberKasId, $tanggal);
                $saldoSesudah = $this->calculateSaldoSesudah($saldoSebelum, $tipeTransaksi, $jumlah);

                $data['saldo_sebelum'] = $saldoSebelum;
                $data['saldo_sesudah'] = $saldoSesudah;
            }

            $updated = $this->repository->update($id, $data);

            if (! $updated) {
                return null;
            }

            // Update saldo terakhir if needed
            if ($needRecalculation) {
                $this->repository->updateSaldoTerakhirSumberKas(
                    $sumberKasId,
                    $saldoSesudah
                );
            }

            return $this->getKasPerusahaanById($id);
        });
    }

    public function deleteKasPerusahaan(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $kasPerusahaan = $this->repository->findById($id, false);

            if (! $kasPerusahaan) {
                return false;
            }

            // Delete the record
            $deleted = $this->repository->delete($id);

            if ($deleted) {
                // Recalculate saldo terakhir for the sumber kas
                $this->recalculateSaldoTerakhir($kasPerusahaan->sumber_kas_id);
            }

            return $deleted;
        });
    }

    private function validateSumberKas(string $sumberKasId): void
    {
        $sumberKas = $this->sumberKasService->getSumberKasById($sumberKasId);

        if (! $sumberKas) {
            throw new \InvalidArgumentException('Sumber kas tidak ditemukan');
        }

        if (! $sumberKas->aktif) {
            throw new \InvalidArgumentException('Sumber kas tidak aktif');
        }
    }

    private function calculateSaldoSesudah(float $saldoSebelum, string $tipeTransaksi, float $jumlah): float
    {
        return match ($tipeTransaksi) {
            'DEBIT' => $saldoSebelum + $jumlah,
            'KREDIT' => $saldoSebelum - $jumlah,
            default => throw new \InvalidArgumentException('Tipe transaksi tidak valid')
        };
    }

    private function recalculateSaldoTerakhir(string $sumberKasId): void
    {
        // Get the latest record for this sumber kas
        $latestRecord = $this->repository->getAllPaginated(['sumber_kas_id' => $sumberKasId], 1);

        if ($latestRecord->count() > 0) {
            $lastSaldo = $latestRecord->first()->saldo_sesudah;
        } else {
            // If no records, get saldo_awal from sumber_kas
            $sumberKas = $this->sumberKasService->getSumberKasById($sumberKasId);
            $lastSaldo = $sumberKas ? $sumberKas->saldoAwal : 0;
        }

        $this->repository->updateSaldoTerakhirSumberKas($sumberKasId, $lastSaldo);
    }

    public function getSaldoPerSumberKas(): array
    {
        // Logic to get saldo per sumber kas
        // This would require a custom query or use of Eloquent relationships
        // For now, returning empty array
        return [];
    }
}
