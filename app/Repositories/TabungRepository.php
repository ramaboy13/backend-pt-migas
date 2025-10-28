<?php

namespace App\Repositories;

use App\Models\Tabung;
use Illuminate\Pagination\LengthAwarePaginator;

class TabungRepository
{
  public function __construct(private Tabung $model) {}

  // Ambil semua data tabung (dengan pagination)
  public function getAllPaginated(int $perPage = 10): LengthAwarePaginator
  {
    return $this->model->newQuery()
      ->orderBy('created_at', 'desc')
      ->paginate($perPage);
  }

  // Cari tabung by ID
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

  // Cek apakah nama tabung sudah ada (untuk avoid duplikat)
  public function findByName(string $name): ?Tabung
  {
    return $this->model->where('name', $name)->first();
  }
}
