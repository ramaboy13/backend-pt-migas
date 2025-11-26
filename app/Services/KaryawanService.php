<?php

namespace App\Services;

use App\DTO\Karyawan\KaryawanDTO;
use App\DTO\Karyawan\KaryawanCollectionDTO;
use App\Repositories\KaryawanRepository;

class KaryawanService
{
    public function __construct(private KaryawanRepository $repository) {}

    public function getAllKaryawan(array $filters = []): KaryawanCollectionDTO
    {
        $karyawans = $this->repository->getAll($filters);
        return KaryawanCollectionDTO::fromPaginator($karyawans);
    }

    public function getKaryawanById(string $id): ?KaryawanDTO
    {
        $karyawan = $this->repository->findById($id);

        if (!$karyawan) {
            return null;
        }

        return KaryawanDTO::fromModel($karyawan);
    }

    public function createKaryawan(array $data): KaryawanDTO
    {
        $karyawan = $this->repository->create($data);
        return KaryawanDTO::fromModel($karyawan);
    }

    public function updateKaryawan(string $id, array $data): ?KaryawanDTO
    {
        $updated = $this->repository->update($id, $data);

        if (!$updated) {
            return null;
        }

        $karyawan = $this->repository->findById($id);
        return KaryawanDTO::fromModel($karyawan);
    }

    public function deleteKaryawan(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
