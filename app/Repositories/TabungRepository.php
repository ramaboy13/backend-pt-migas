<?php

namespace App\Repositories;

use App\Models\Tabung;
use Illuminate\Pagination\LengthAwarePaginator;

class TabungRepository
{
  public function __construct(private Tabung $model) {}

  public function getAllPaginated(int $perPage = 10): LengthAwarePaginator
  {
    return $this->model->newQuery()
      ->orderBy('created_at', 'desc')
      ->paginate($perPage);
  }

  public function findById(string $id): ?Tabung
  {
    return $this->model->find($id);
  }

  // Buat tabung baru
  public function create(array $data): Tabung
  {
    return $this->model->create($data);
  }

  // Update data tabung
  public function update(string $id, array $data): bool
  {
    return $this->model->where('id', $id)->update($data);
  }

  // Hapus tabung
  public function delete(string $id): bool
  {
    return $this->model->where('id', $id)->delete();
  }

  public function findByName(string $nama): ?Tabung
  {
    return $this->model->where('nama', $nama)->first();
  }
}
