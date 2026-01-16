<?php

namespace App\Repositories;

use App\Models\GajiKaryawan;
use Illuminate\Pagination\LengthAwarePaginator;

class GajiKaryawanRepository
{
    public function __construct(private GajiKaryawan $model) {}

    public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->with(['karyawan', 'pendapatan', 'potongan']);

        // Filter by periode
        if (! empty($filters['periode'])) {
            $query->where('periode', $filters['periode']);
        }

        // Filter by range periode
        if (! empty($filters['start_periode']) && ! empty($filters['end_periode'])) {
            $query->whereBetween('periode', [$filters['start_periode'], $filters['end_periode']]);
        }

        // Filter by karyawan_id
        // if (! empty($filters['karyawan_id'])) {
        //     $query->where('karyawan_id', $filters['karyawan_id']);
        // }

        // Filter by nama karyawan
        if (! empty($filters['nama_karyawan'])) {
            $query->whereHas('karyawan', function ($q) use ($filters) {
                $q->where('nama', 'LIKE', '%'.$filters['nama_karyawan'].'%');
            });
        }

        // Filter by status aktif karyawan
        if (isset($filters['karyawan_aktif'])) {
            $query->whereHas('karyawan', function ($q) use ($filters) {
                $q->where('aktif', $filters['karyawan_aktif']);
            });
        }

        return $query->orderBy('periode', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(string $id): ?GajiKaryawan
    {
        return $this->model->with(['karyawan', 'pendapatan', 'potongan'])->find($id);
    }

    public function findByKaryawanAndPeriode(string $karyawanId, string $periode): ?GajiKaryawan
    {
        return $this->model->where('karyawan_id', $karyawanId)
            ->where('periode', $periode)
            ->first();
    }

    public function create(array $data): GajiKaryawan
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
        $query = $this->model->with(['pendapatan', 'potongan'])
            ->where('karyawan_id', $karyawanId);

        if (! empty($filters['start_periode']) && ! empty($filters['end_periode'])) {
            $query->whereBetween('periode', [$filters['start_periode'], $filters['end_periode']]);
        }

        return $query->orderBy('periode', 'desc')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function getSummaryByPeriode(string $periode): array
    {
        return $this->model->where('periode', $periode)
            ->selectRaw('
                              COUNT(*) as total_karyawan,
                              SUM(subtotal) as total_subtotal,
                              SUM(pph21) as total_pph21,
                              SUM(gaji_bersih) as total_gaji_bersih
                          ')
            ->first()
            ->toArray();
    }
}
