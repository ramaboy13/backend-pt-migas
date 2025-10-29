<?php

namespace App\Repositories;

use App\Models\LemburKaryawan;
use Illuminate\Pagination\LengthAwarePaginator;

class LemburKaryawanRepository
{
  public function __construct(private LemburKaryawan $model) {}

  public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
  {
    $query = $this->model->with('karyawan');

    // Filter by tanggal
    if (!empty($filters['tanggal'])) {
      $query->where('tanggal', $filters['tanggal']);
    }

    // Filter by range tanggal
    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
      $query->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']]);
    }

    // Filter by karyawan_id
    if (!empty($filters['karyawan_id'])) {
      $query->where('karyawan_id', $filters['karyawan_id']);
    }

    // Filter by nama karyawan (via relationship)
    if (!empty($filters['nama_karyawan'])) {
      $query->whereHas('karyawan', function ($q) use ($filters) {
        $q->where('nama', 'LIKE', '%' . $filters['nama_karyawan'] . '%');
      });
    }

    return $query->orderBy('tanggal', 'desc')
      ->orderBy('created_at', 'desc')
      ->paginate($perPage);
  }

  public function findById(string $id): ?LemburKaryawan
  {
    return $this->model->with('karyawan')->find($id);
  }

  public function create(array $data): LemburKaryawan
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

  public function getByKaryawanId(string $karyawanId, array $filters = []): LengthAwarePaginator
  {
    $query = $this->model->where('karyawan_id', $karyawanId);

    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
      $query->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']]);
    }

    return $query->orderBy('tanggal', 'desc')->paginate($filters['per_page'] ?? 10);
  }
}
