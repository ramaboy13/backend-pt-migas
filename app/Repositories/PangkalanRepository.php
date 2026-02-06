<?php

namespace App\Repositories;

use App\Models\Pangkalan;
use Illuminate\Pagination\LengthAwarePaginator;

class PangkalanRepository
{
  public function __construct(private Pangkalan $model) {}

  public function getAllPaginated(int $perPage = 10, array $filters = []): LengthAwarePaginator
  {
    $query = $this->model->newQuery();

    // filter multiple collumn
    if(!empty($filters['search'])) {
        $search = $filters['search'];
        $query->where(function ($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
            ->orWhere('alamat', 'like', "%{$search}%")
            ->orWhere('no_ktp', 'like', "%{$search}%");
        });
    }

    return $query->orderBy('created_at', 'desc')
      ->paginate($perPage);
  }

  public function findById(string $id): ?Pangkalan
  {
    return $this->model->find($id);
  }

  public function findByRegistId(string $registId): ?Pangkalan
  {
    return $this->model->where('regist_id', $registId)->first();
  }

  public function findByKtp(string $noKtp): ?Pangkalan
  {
    return $this->model->where('no_ktp', $noKtp)->first();
  }

  public function create(array $data): Pangkalan
  {
    return $this->model->create($data);
  }

  public function update(string $id, array $data): bool
  {
    return $this->model->where('id', $id)->update($data);
  }

  public function delete(string $id): bool
  {
    return $this->model->where('id', $id)->delete();
  }
}
