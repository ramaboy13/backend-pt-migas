<?php

namespace App\Repositories;

use App\Models\Pendapatan;
use Illuminate\Pagination\LengthAwarePaginator;

class PendapatanRepository
{
    public function __construct(private Pendapatan $model) {}

    public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->with('karyawan');

        // Filter by periode
        if (! empty($filters['periode'])) {
            $query->where('periode', $filters['periode']);
        }

        // Filter by range periode
        if (! empty($filters['start_periode']) && ! empty($filters['end_periode'])) {
            $query->whereBetween('periode', [$filters['start_periode'], $filters['end_periode']]);
        }

        // Filter by karyawan_id
        // if (!empty($filters['karyawan_id'])) {
        //   $query->where('karyawan_id', $filters['karyawan_id']);
        // }

        // Filter by nama karyawan (via relationship)
        if (! empty($filters['nama_karyawan'])) {
            $query->whereHas('karyawan', function ($q) use ($filters) {
                $q->where('nama', 'LIKE', '%'.$filters['nama_karyawan'].'%');
            });
        }

        return $query->orderBy('periode', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(string $id): ?Pendapatan
    {
        return $this->model->with('karyawan')->find($id);
    }

    public function findByKaryawanAndPeriode(string $karyawanId, string $periode): ?Pendapatan
    {
        return $this->model->where('karyawan_id', $karyawanId)
            ->where('periode', $periode)
            ->first();
    }

    public function create(array $data): Pendapatan
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

        if (! empty($filters['start_periode']) && ! empty($filters['end_periode'])) {
            $query->whereBetween('periode', [$filters['start_periode'], $filters['end_periode']]);
        }

        return $query->orderBy('periode', 'desc')
            ->paginate($filters['per_page'] ?? 10);
    }
}
