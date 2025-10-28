<?php

namespace App\Services;

use App\Repositories\TabungRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class TabungService
{
  public function __construct(private TabungRepository $repository) {}

  public function getAllTabung(int $perPage = 15): LengthAwarePaginator
  {
    return $this->repository->getAllPaginated($perPage);
  }

  public function getTabungById(string $id): ?array
  {
    $tabung = $this->repository->findById($id);
    return $tabung ? $tabung->toArray() : null;
  }

  public function createTabung(array $data): array
  {
    // Validasi business logic
    $this->validateTabungData($data);

    $tabung = $this->repository->create($data);

    return $tabung->toArray();
  }


  public function updateTabung(string $id, array $data): ?array
  {
    // Validasi business logic
    $this->validateTabungData($data);

    $updated = $this->repository->update($id, $data);

    if (!$updated) {
      return null;
    }

    return $this->getTabungById($id);
  }

  public function deleteTabung(string $id): bool
  {
    return $this->repository->delete($id);
  }

  private function validateTabungData(array $data): void
  {
    // Validasi nama tabung unik
    if ($this->repository->findByName($data['name'])) {
      throw new \InvalidArgumentException('Tabung name must be unique');
    }
    // Validasi berat harus positif
    if (isset($data['berat']) && $data['berat'] <= 0) {
      throw new \InvalidArgumentException('Berat must be positive');
    }
  }
}
