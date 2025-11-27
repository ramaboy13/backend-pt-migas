<?php
// app/Services/PangkalanService.php

namespace App\Services;

use App\Repositories\PangkalanRepository;
use App\DTO\Pangkalan\PangkalanDTO;
use App\DTO\Pangkalan\PangkalanCollectionDTO;

class PangkalanService
{
  public function __construct(private PangkalanRepository $repository) {}

  public function getAllPangkalan(int $perPage = 15, array $filters = []): PangkalanCollectionDTO
  {
    $result = $this->repository->getAllPaginated($perPage, $filters);
    return PangkalanCollectionDTO::fromPaginator($result);
  }

  public function getPangkalanById(string $id): ?PangkalanDTO
  {
    $pangkalan = $this->repository->findById($id);

    if (!$pangkalan) {
      return null;
    }

    return PangkalanDTO::fromModel($pangkalan);
  }

  public function createPangkalan(array $data): PangkalanDTO
  {
    // Validasi business logic
    $this->validatePangkalanData($data);

    $pangkalan = $this->repository->create($data);
    return PangkalanDTO::fromModel($pangkalan);
  }

  public function updatePangkalan(string $id, array $data): ?PangkalanDTO
  {
    $this->validatePangkalanData($data, false);

    $updated = $this->repository->update($id, $data);

    if (!$updated) {
      return null;
    }

    // Return data terbaru
    $updatedPangkalan = $this->repository->findById($id);
    return $updatedPangkalan ? PangkalanDTO::fromModel($updatedPangkalan) : null;
  }

  public function deletePangkalan(string $id): bool
  {
    return $this->repository->delete($id);
  }

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
