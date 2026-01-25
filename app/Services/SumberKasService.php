<?php

namespace App\Services;

use App\DTO\SumberKas\SumberKasCollectionDTO;
use App\DTO\SumberKas\SumberKasDTO;
use App\Models\SumberKas;
use App\Repositories\SumberKasRepository;

class SumberKasService
{
    public function __construct(private SumberKasRepository $repository) {}

    public function getAllSumberKas(array $filters = [], int $perPage = 10): SumberKasCollectionDTO
    {
        $paginator = $this->repository->getAllPaginated($filters, $perPage);

        return SumberKasCollectionDTO::fromPaginator($paginator);
    }

    public function getSumberKasById(string $id): ?SumberKasDTO
    {
        $sumberKas = $this->repository->findById($id);

        if (! $sumberKas) {
            return null;
        }

        return SumberKasDTO::fromModel($sumberKas);
    }

    public function createSumberKas(array $data): SumberKasDTO
    {
        // Validate data
        $this->validateSumberKasData($data);

        // Create sumber kas
        $sumberKas = $this->repository->create($data);

        return SumberKasDTO::fromModel($sumberKas);
    }

    public function updateSumberKas(string $id, array $data): ?SumberKasDTO
    {
        // Check if sumber kas exists
        $existing = $this->repository->findById($id);

        if (! $existing) {
            return null;
        }

        // Validate data
        $this->validateSumberKasData($data, $existing);

        // Update sumber kas
        $updated = $this->repository->update($id, $data);

        if (! $updated) {
            return null;
        }

        return $this->getSumberKasById($id);
    }

    public function deleteSumberKas(string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function restoreSumberKas(string $id): bool
    {
        return $this->repository->restore($id);
    }

    public function getAllActiveSumberKas(): array
    {
        $sumberKasList = $this->repository->getAllActive();

        return $sumberKasList->map(function ($sumberKas) {
            return SumberKasDTO::fromModel($sumberKas);
        })->toArray();
    }

    public function getBanks(): array
    {
        $banks = $this->repository->getAllBanks();

        return $banks->map(function ($bank) {
            return SumberKasDTO::fromModel($bank);
        })->toArray();
    }

    public function getCash(): array
    {
        $cash = $this->repository->getAllCash();

        return $cash->map(function ($cashItem) {
            return SumberKasDTO::fromModel($cashItem);
        })->toArray();
    }

    public function getTotalSaldo(): float
    {
        return $this->repository->getTotalSaldo();
    }

    public function getSaldoByTipe(string $tipe): float
    {
        return $this->repository->getSaldoByTipe($tipe);
    }

    public function getSaldoSummary(): array
    {
        return [
            'total_saldo' => $this->getTotalSaldo(),
            'saldo_bank' => $this->getSaldoByTipe('BANK'),
            'saldo_cash' => $this->getSaldoByTipe('CASH'),
        ];
    }

    public function updateSaldo(string $id, float $saldoBaru): bool
    {
        return $this->repository->updateSaldo($id, $saldoBaru);
    }

    private function validateSumberKasData(array $data, ?SumberKas $existing = null): void
    {
        // Validate unique nomor rekening for BANK type
        if (($data['tipe'] ?? $existing?->tipe) === 'BANK') {
            $nomorRekening = $data['nomor_rekening'] ?? $existing?->nomor_rekening;

            if (! empty($nomorRekening)) {
                $existingRecord = $this->repository->findByNomorRekening($nomorRekening);

                if ($existingRecord && $existingRecord->id !== ($existing?->id)) {
                    throw new \InvalidArgumentException('Nomor rekening sudah terdaftar');
                }
            }
        }

        // Validate saldo awal
        if (isset($data['saldo_awal']) && $data['saldo_awal'] < 0) {
            throw new \InvalidArgumentException('Saldo awal tidak boleh negatif');
        }
    }

    public function validateSumberKasActive(string $id): bool
    {
        $sumberKas = $this->repository->findById($id);

        return $sumberKas && $sumberKas->aktif;
    }
}
