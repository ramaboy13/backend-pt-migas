<?php

namespace App\Services;

use App\Repositories\TabungRepository;
use App\DTO\Tabung\TabungDTO;
use App\DTO\Tabung\TabungCollectionDTO;


class TabungService
{
  public function __construct(private TabungRepository $repository) {}

  public function getAllTabung(int $perPage = 10): TabungCollectionDTO
  {
    $result = $this->repository->getAllPaginated($perPage);
    return TabungCollectionDTO::fromPaginator($result);
  }

  public function getTabungById(string $id): ?TabungDTO
  {
    $tabung = $this->repository->findById($id);
    if (!$tabung) {
      return null;
    }
    return TabungDTO::fromModel($tabung);
  }

  public function createTabung(array $data): TabungDTO
  {
    // Validasi business logic
    $this->validateTabungData($data);
    $tabung = $this->repository->create($data);
    return TabungDTO::fromModel($tabung);
  }

  public function updateTabung(string $id, array $data): ?TabungDTO
  {
    // Validasi business logic
    $this->validateTabungData($data, $id);
    $updated = $this->repository->update($id, $data);
    if (!$updated) {
      return null;
    }
    // Return data terbaru
    $updatedTabung = $this->repository->findById($id);
    return $updatedTabung ? TabungDTO::fromModel($updatedTabung) : null;
  }

  public function deleteTabung(string $id): bool
  {
    return $this->repository->delete($id);
  }

  private function validateTabungData(array $data, ?string $id = null): void
  {
    // Validasi nama tabung unik (untuk create dan update)
    $existingTabung = $this->repository->findByName($data['nama']);

    if ($existingTabung) {
      // Untuk update, allow jika ID sama (update record yang sama)
      if ($id && $existingTabung->id === $id) {
        return;
      }
      throw new \InvalidArgumentException('Nama tabung harus unik');
    }

    // Validasi berat harus positif
    if (isset($data['berat']) && $data['berat'] <= 0) {
      throw new \InvalidArgumentException('Berat tabung harus positif');
    }
  }
}
