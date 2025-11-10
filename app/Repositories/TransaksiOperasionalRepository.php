<?php

namespace App\Repositories;

use App\Models\TransaksiOperasional;
use Illuminate\Pagination\LengthAwarePaginator;

class TransaksiOperasionalRepository
{
  public function __construct(private TransaksiOperasional $model) {}

  public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
  {
    $query = $this->model->with(['pangkalan', 'tabung']);

    // Filter by tanggal
    if (!empty($filters['tanggal'])) {
      $query->where('tanggal', $filters['tanggal']);
    }

    // Filter by range tanggal
    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
      $query->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']]);
    }

    // Filter by pangkalan_id
    if (!empty($filters['pangkalan_id'])) {
      $query->where('pangkalan_id', $filters['pangkalan_id']);
    }

    // Filter by tabung_id
    if (!empty($filters['tabung_id'])) {
      $query->where('tabung_id', $filters['tabung_id']);
    }

    // Filter by jenis transaksi (is_in)
    if (isset($filters['is_in'])) {
      $query->where('is_in', $filters['is_in']);
    }

    return $query->orderBy('tanggal', 'desc')
      ->orderBy('created_at', 'desc')
      ->paginate($perPage);
  }

  public function findById(string $id): ?TransaksiOperasional
  {
    return $this->model->with(['pangkalan', 'tabung'])->find($id);
  }

  public function findByNoRef(string $noRef): ?TransaksiOperasional
  {
    return $this->model->where('no_ref', $noRef)->first();
  }

  public function create(array $data): TransaksiOperasional
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

  public function getByPangkalan(string $pangkalanId, array $filters = []): LengthAwarePaginator
  {
    $query = $this->model->with('tabung')
      ->where('pangkalan_id', $pangkalanId);

    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
      $query->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']]);
    }

    if (isset($filters['is_in'])) {
      $query->where('is_in', $filters['is_in']);
    }

    return $query->orderBy('tanggal', 'desc')
      ->paginate($filters['per_page'] ?? 10);
  }
}
