<?php

namespace App\Services;

use App\Repositories\KaryawanRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class KaryawanService
{
    public function __construct(private KaryawanRepository $repository) {}

    public function getAllKaryawan(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getAll($filters);
    }

    public function getKaryawanById(string $id): ?array
    {
        $karyawan = $this->repository->findById($id);
        return $karyawan ? $karyawan->toArray() : null;
    }

    public function createKaryawan(array $data): array
    {

        $karyawan = $this->repository->create($data);
        return $karyawan->toArray();
    }

    public function updateKaryawan(string $id, array $data): ?array
    {
        $updated = $this->repository->update($id, $data);

        if (!$updated) {
            return null;
        }

        return $this->repository->findById($id)->toArray();
    }

    public function deleteKaryawan(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
