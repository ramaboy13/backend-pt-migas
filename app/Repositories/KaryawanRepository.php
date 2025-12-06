<?php

namespace App\Repositories;

use App\Models\Karyawan;
use Illuminate\Pagination\LengthAwarePaginator;

class KaryawanRepository
{
    public function __construct(private Karyawan $model) {}

    public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Filter by nama
        if (!empty($filters['nama'])) {
            $query->where('nama', 'LIKE', '%' . $filters['nama'] . '%');
        }

        // Filter by jabatan
        if (!empty($filters['jabatan'])) {
            $query->where('jabatan', 'LIKE', '%' . $filters['jabatan'] . '%');
        }

        // Filter by status aktif
        if (isset($filters['aktif'])) {
            $query->where('aktif', $filters['aktif']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(string $id): ?Karyawan
    {
        return $this->model->find($id);
    }

    public function findByNIK(string $nik): ?Karyawan
    {
        return $this->model->where('NIK', $nik)->first();
    }

    public function create(array $data): Karyawan
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
