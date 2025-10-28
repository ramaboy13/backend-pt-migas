<?php
// app/Services/KasBcaService.php

namespace App\Services;

use App\Repositories\KasBcaRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class KasBcaService
{
    public function __construct(private KasBcaRepository $repository) {}

    public function getAllKasBca(int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($perPage);
    }

    public function getKasBcaById(string $id): ?array
    {
        return $this->repository->findById($id);
    }

    // public function getKasBcaByDateRange(string $startDate, string $endDate): Collection
    // {
    //     return $this->repository->getByDateRange($startDate, $endDate);
    // }

    public function createKasBca(array $data): array
    {
        // Business logic validation
        $this->validateKasBcaData($data);

        return $this->repository->create($data);
    }

    public function updateKasBca(string $id, array $data): ?array
    {
        $this->validateKasBcaData($data);

        return $this->repository->update($id, $data);
    }

    public function deleteKasBca(string $id): bool
    {
        return $this->repository->delete($id);
    }

    private function validateKasBcaData(array $data): void
    {
        if (isset($data['debit']) && $data['debit'] < 0) {
            throw new \InvalidArgumentException('Debit cannot be negative');
        }

        if (isset($data['kredit']) && $data['kredit'] < 0) {
            throw new \InvalidArgumentException('Kredit cannot be negative');
        }

        if (
            isset($data['debit']) && isset($data['kredit']) &&
            $data['debit'] > 0 && $data['kredit'] > 0
        ) {
            throw new \InvalidArgumentException('Either debit or kredit must be zero');
        }
    }
}
