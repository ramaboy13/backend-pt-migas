<?php

// app/Repositories/PotonganRepository.php

namespace App\Repositories;

use App\Models\Karyawan;
use App\Models\Potongan;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class PotonganRepository
{
    public function __construct(
        private Potongan $potonganKaryawan,
        private Karyawan $karyawanModel
    ) {}

    public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->potonganKaryawan->with('karyawan');

        // Filter by periode
        if (! empty($filters['periode'])) {
            $query->where('periode', $filters['periode']);
        }

        // Filter by range periode
        if (! empty($filters['start_periode']) && ! empty($filters['end_periode'])) {
            $query->whereBetween('periode', [$filters['start_periode'], $filters['end_periode']]);
        }

        // Filter by karyawan_id
        if (! empty($filters['karyawan_id'])) {
            $query->where('karyawan_id', $filters['karyawan_id']);
        }

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

    public function findById(string $id): ?Potongan
    {
        return $this->potonganKaryawan->with('karyawan')->find($id);
    }

    public function findByKaryawanAndPeriode(string $karyawanId, string $periode): ?Potongan
    {
        return $this->potonganKaryawan->where('karyawan_id', $karyawanId)
            ->where('periode', $periode)
            ->first();
    }

    public function create(array $data): Potongan
    {
        $karyawan = $this->karyawanModel->find($data['karyawan_id']);
        if (! $karyawan) {
            throw new ModelNotFoundException('Karyawan tidak ditemukan');
        }

        if (! $karyawan->aktif) {
            throw new \InvalidArgumentException('Tidak dapat menambahkan potongan untuk karyawan yang tidak aktif '.
                  "Karyawan {$karyawan->nama} (NIK: {$karyawan->nik}) berstatus tidak aktif.");
        }
        if (isset($data['periode']) && $karyawan->tgl_masuk) {
            $tanggalPendapatan = \Carbon\Carbon::parse($data['periode']);
            $tanggalMasuk = \Carbon\Carbon::parse($karyawan->tgl_masuk);

            if ($tanggalPendapatan->lt($tanggalMasuk)) {
                throw new \InvalidArgumentException(
                    'Periode potongan tidak boleh sebelum tanggal masuk karyawan. '.
                    "Tanggal masuk: {$karyawan->tgl_masuk}"
                );
            }
        }

        return $this->potonganKaryawan->create($data);
    }

    public function update(string $id, array $data): bool
    {
        return $this->potonganKaryawan->where('id', $id)->update($data);
    }

    public function delete(string $id): bool
    {
        return $this->potonganKaryawan->where('id', $id)->delete();
    }

    public function getByKaryawanId(string $karyawanId, array $filters = []): LengthAwarePaginator
    {
        $query = $this->potonganKaryawan->where('karyawan_id', $karyawanId);

        if (! empty($filters['start_periode']) && ! empty($filters['end_periode'])) {
            $query->whereBetween('periode', [$filters['start_periode'], $filters['end_periode']]);
        }

        return $query->orderBy('periode', 'desc')
            ->paginate($filters['per_page'] ?? 10);
    }
}
