<?php

namespace App\Repositories;

use App\Models\Karyawan;
use App\Models\Pendapatan;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class PendapatanRepository
{
    public function __construct(
        private Pendapatan $pendapatanKaryawan,
        private Karyawan $karyawanModel
    ) {}

    public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->pendapatanKaryawan->with('karyawan');

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

        // Filter multiple column
        if(!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('karyawan', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('periode', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(string $id): ?Pendapatan
    {
        return $this->pendapatanKaryawan->with('karyawan')->find($id);
    }

    public function findByKaryawanAndPeriode(string $karyawanId, string $periode): ?Pendapatan
    {
        return $this->pendapatanKaryawan->where('karyawan_id', $karyawanId)
            ->where('periode', $periode)
            ->first();
    }

    public function create(array $data): Pendapatan
    {
        $karyawan = $this->karyawanModel->find($data['karyawan_id']);
        if (! $karyawan) {
            throw new ModelNotFoundException('Karyawan not found');
        }

        if (! $karyawan->aktif) {
            throw new \InvalidArgumentException(
                'Tidak dapat membuat data pendapatan untuk karyawan yang tidak aktif. '.
                "Karyawan {$karyawan->nama} (NIK: {$karyawan->nik}) berstatus tidak aktif."
            );
        }

        if (isset($data['periode']) && $karyawan->tgl_masuk) {
            $tanggalPendapatan = \Carbon\Carbon::parse($data['periode']);
            $tanggalMasuk = \Carbon\Carbon::parse($karyawan->tgl_masuk);

            if ($tanggalPendapatan->lt($tanggalMasuk)) {
                throw new \InvalidArgumentException(
                    'Periode pendapatan tidak boleh sebelum tanggal masuk karyawan. '.
                    "Tanggal masuk: {$karyawan->tgl_masuk}"
                );
            }
        }

        return $this->pendapatanKaryawan->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $pendapatanKaryawan = $this->pendapatanKaryawan->find($id);
        if (! $pendapatanKaryawan) {
            throw new ModelNotFoundException('Pendapatan Karyawan tidak ditemukan');
        }

        if (isset($data['periode']) && $pendapatanKaryawan->karyawan->tgl_masuk) {
            $tanggalPendapatan = \Carbon\Carbon::parse($data['periode']);
            $tanggalMasuk = \Carbon\Carbon::parse($pendapatanKaryawan->karyawan->tgl_masuk);

            if ($tanggalPendapatan->lt($tanggalMasuk)) {
                throw new \InvalidArgumentException(
                    'Periode pendapatan tidak boleh sebelum tanggal masuk karyawan. '.
                    "Tanggal masuk: {$pendapatanKaryawan->karyawan->tgl_masuk}"
                );
            }
        }

        return $this->pendapatanKaryawan->update($data);
    }

    public function delete(string $id): bool
    {
        return $this->pendapatanKaryawan->where('id', $id)->delete();
    }

    public function getByKaryawanId(string $karyawanId, array $filters = []): LengthAwarePaginator
    {
        $query = $this->pendapatanKaryawan->where('karyawan_id', $karyawanId);

        if (! empty($filters['start_periode']) && ! empty($filters['end_periode'])) {
            $query->whereBetween('periode', [$filters['start_periode'], $filters['end_periode']]);
        }

        return $query->orderBy('periode', 'desc')
            ->paginate($filters['per_page'] ?? 10);
    }
}
