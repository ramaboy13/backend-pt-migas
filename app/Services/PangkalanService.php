<?php

namespace App\Services;

use App\Repositories\PangkalanRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class PangkalanService
{
  public function __construct(private PangkalanRepository $repository) {}

  public function getAllPangkalan(int $perPage = 15, array $filters = []): LengthAwarePaginator
  {
    return $this->repository->getAllPaginated($perPage, $filters);
  }

  public function getPangkalanById(string $id): ?array
  {
    $pangkalan = $this->repository->findById($id);

    if (!$pangkalan) {
      return null;
    }

    return $pangkalan->toArray();
  }


  public function createPangkalan(array $data): array
  {
    // Validasi business logic
    $this->validatePangkalanData($data);

    $pangkalan = $this->repository->create($data);
    return $pangkalan->toArray();
  }


  public function updatePangkalan(string $id, array $data): ?array
  {
    $this->validatePangkalanData($data, false);

    $updated = $this->repository->update($id, $data);

    if (!$updated) {
      return null;
    }

    // Return data terbaru
    return $this->getPangkalanById($id);
  }

  // Hapus pangkalan
  public function deletePangkalan(string $id): bool
  {
    return $this->repository->delete($id);
  }

  // Validasi data pangkalan
  private function validatePangkalanData(array $data, bool $isCreate = true): void
  {
    // Untuk create, pastikan regist_id dan no_ktp unik
    if ($isCreate) {
      if ($this->repository->findByRegistId($data['regist_id'])) {
        throw new \InvalidArgumentException('Registration ID already exists');
      }

      if ($this->repository->findByKtp($data['no_ktp'])) {
        throw new \InvalidArgumentException('KTP number already exists');
      }
    }

    // Validasi harga satuan jika ada
    if (isset($data['harga_satuan']) && $data['harga_satuan'] < 0) {
      throw new \InvalidArgumentException('Unit price cannot be negative');
    }
  }
}
