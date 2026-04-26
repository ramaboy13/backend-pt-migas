<?php

namespace App\Repositories;

use App\Models\Karyawan;
use App\Models\KomponenGaji;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class KomponenGajiRepository
{
    public function __construct(
        private KomponenGaji $model,
        private Karyawan $karyawanModel
    ) {}

    public function getAll(array $filters = [], int $perPage = 10, bool $withKaryawan = true): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Load relations
        if ($withKaryawan) {
            $query->with('karyawan');
        }

        // Filter by tipe
        if (! empty($filters['tipe'])) {
            $query->where('tipe', $filters['tipe']);
        }

        // Filter by tanggal
        if (! empty($filters['tanggal'])) {
            $query->where('tanggal', $filters['tanggal']);
        }

        // Filter by range tanggal
        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']]);
        }

        // Filter by periode (bulan/tahun)
        if (! empty($filters['bulan']) && ! empty($filters['tahun'])) {
            $query->whereYear('tanggal', $filters['tahun'])
                ->whereMonth('tanggal', $filters['bulan']);
        }

        // Filter by karyawan_id
        if (! empty($filters['karyawan_id'])) {
            $query->where('karyawan_id', $filters['karyawan_id']);
        }

        // Search by karyawan name or keterangan
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('karyawan', function ($sub) use ($search) {
                        $sub->where('nama', 'like', "%{$search}%")
                            ->orWhere('NIK', 'like', "%{$search}%");
                    });
            });
        }

        // Order by
        $query->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    public function findById(string $id, bool $withKaryawan = true): ?KomponenGaji
    {
        $query = $this->model->newQuery();

        if ($withKaryawan) {
            $query->with('karyawan');
        }

        return $query->find($id);
    }

    public function create(array $data): KomponenGaji
    {
        // Validasi: Cek apakah karyawan aktif
        $karyawan = $this->karyawanModel->find($data['karyawan_id']);

        if (! $karyawan) {
            throw new ModelNotFoundException("Karyawan dengan ID {$data['karyawan_id']} tidak ditemukan");
        }

        if (! $karyawan->aktif) {
            throw new \InvalidArgumentException(
                'Tidak dapat membuat komponen gaji untuk karyawan yang tidak aktif. '.
                "Karyawan {$karyawan->nama} (NIK: {$karyawan->NIK}) berstatus tidak aktif."
            );
        }

        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $komponen = $this->findById($id, false);

        if (! $komponen) {
            throw new ModelNotFoundException("Komponen gaji dengan ID {$id} tidak ditemukan");
        }

        return $komponen->update($data);
    }

    public function delete(string $id): bool
    {
        $komponen = $this->findById($id, false);

        if (! $komponen) {
            return false;
        }

        return $komponen->delete();
    }

    public function getByKaryawanId(string $karyawanId, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->where('karyawan_id', $karyawanId);

        if (! empty($filters['tipe'])) {
            $query->where('tipe', $filters['tipe']);
        }

        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']]);
        }

        if (! empty($filters['bulan']) && ! empty($filters['tahun'])) {
            $query->whereYear('tanggal', $filters['tahun'])
                ->whereMonth('tanggal', $filters['bulan']);
        }

        return $query->orderBy('tanggal', 'desc')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function getTotalByPeriode(string $karyawanId, string $tipe, int $bulan, int $tahun): float
    {
        return $this->model->where('karyawan_id', $karyawanId)
            ->where('tipe', $tipe)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->sum('nominal');
    }

    public function getKaryawanInfo(string $karyawanId): array
    {
        $karyawan = $this->karyawanModel->find($karyawanId);

        if (! $karyawan) {
            throw new ModelNotFoundException("Karyawan dengan ID {$karyawanId} tidak ditemukan");
        }

        return [
            'id' => $karyawan->id,
            'nik' => $karyawan->nik,  // PERBAIKAN: lowercase nik
            'nama' => $karyawan->nama,
            'is_active' => $karyawan->aktif,  // PERBAIKAN: aktif
            'tgl_masuk' => $karyawan->tgl_masuk,
            'jabatan' => $karyawan->jabatan,
            'gapok' => $karyawan->gaji_pokok,  // PERBAIKAN: gaji_pokok
            'bpjs_kesehatan' => $karyawan->bpjs_kesehatan,
            'bpjs_tenagakerja' => $karyawan->bpjs_tenagakerja,
        ];
    }
}
