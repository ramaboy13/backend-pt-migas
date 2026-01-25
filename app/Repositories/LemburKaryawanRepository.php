<?php

namespace App\Repositories;

use App\Models\Karyawan;
use App\Models\LemburKaryawan;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class LemburKaryawanRepository
{
    public function __construct(
        private LemburKaryawan $model,
        private Karyawan $karyawanModel
    ) {}

    public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->with('karyawan');

        // Filter by tanggal
        if (! empty($filters['periode'])) {
            $query->where('tanggal', $filters['periode']);
        }

        // Filter by range tanggal
        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']]);
        }

        // // Filter by karyawan_id
        // if (! empty($filters['karyawan_id'])) {
        //     $query->where('karyawan_id', $filters['karyawan_id']);
        // }

        // Filter nama karyawan
        if (! empty($filters['nama_karyawan'])) {
            $query->whereHas('karyawan', function ($q) use ($filters) {
                $q->where('nama', 'LIKE', '%'.$filters['nama_karyawan'].'%');
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
        // Validasi: Cek apakah karyawan aktif
        $karyawan = $this->karyawanModel->find($data['karyawan_id']);

        if (! $karyawan) {
            throw new ModelNotFoundException("Karyawan dengan ID {$data['karyawan_id']} tidak ditemukan");
        }

        if (! $karyawan->aktif) {
            throw new \InvalidArgumentException(
                'Tidak dapat membuat data lembur untuk karyawan yang tidak aktif. '.
                "Karyawan {$karyawan->nama} (NIK: {$karyawan->nik}) berstatus tidak aktif."
            );
        }

        // Validasi tambahan: Cek apakah tanggal lembur setelah tanggal masuk karyawan
        if (isset($data['tanggal']) && $karyawan->tgl_masuk) {
            $tanggalLembur = \Carbon\Carbon::parse($data['tanggal']);
            $tanggalMasuk = \Carbon\Carbon::parse($karyawan->tgl_masuk);

            if ($tanggalLembur->lt($tanggalMasuk)) {
                throw new \InvalidArgumentException(
                    'Tanggal lembur tidak boleh sebelum tanggal masuk karyawan. '.
                    "Tanggal masuk: {$karyawan->tgl_masuk}"
                );
            }
        }

        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        // Cari data lembur yang akan diupdate
        $lemburKaryawan = $this->findById($id);

        if (! $lemburKaryawan) {
            throw new ModelNotFoundException("Data lembur dengan ID {$id} tidak ditemukan");
        }

        // Jika ada perubahan karyawan_id, validasi karyawan baru
        if (isset($data['karyawan_id']) && $data['karyawan_id'] !== $lemburKaryawan->karyawan_id) {
            $karyawanBaru = $this->karyawanModel->find($data['karyawan_id']);

            if (! $karyawanBaru) {
                throw new ModelNotFoundException("Karyawan dengan ID {$data['karyawan_id']} tidak ditemukan");
            }

            if (! $karyawanBaru->aktif) {
                throw new \InvalidArgumentException(
                    'Tidak dapat mengubah data lembur ke karyawan yang tidak aktif. '.
                    "Karyawan {$karyawanBaru->nama} (NIK: {$karyawanBaru->nik}) berstatus tidak aktif."
                );
            }

            // Validasi tanggal jika ada perubahan tanggal
            if (isset($data['tanggal']) && $karyawanBaru->tgl_masuk) {
                $tanggalLembur = \Carbon\Carbon::parse($data['tanggal']);
                $tanggalMasuk = \Carbon\Carbon::parse($karyawanBaru->tgl_masuk);

                if ($tanggalLembur->lt($tanggalMasuk)) {
                    throw new \InvalidArgumentException(
                        'Tanggal lembur tidak boleh sebelum tanggal masuk karyawan. '.
                        "Tanggal masuk: {$karyawanBaru->tgl_masuk}"
                    );
                }
            }
        }

        return $lemburKaryawan->update($data);
    }

    public function delete(string $id): bool
    {
        return $this->model->where('id', $id)->delete();
    }

    public function getByKaryawanId(string $karyawanId, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->where('karyawan_id', $karyawanId);

        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->orderBy('tanggal', 'desc')->paginate($filters['per_page'] ?? 10);
    }

    public function getKaryawanInfo(string $karyawanId): array
    {
        $karyawan = $this->karyawanModel->find($karyawanId);

        if (! $karyawan) {
            throw new ModelNotFoundException("Karyawan dengan ID {$karyawanId} tidak ditemukan");
        }

        return [
            'id' => $karyawan->id,
            'nik' => $karyawan->nik,
            'nama' => $karyawan->nama,
            'aktif' => $karyawan->aktif,
            'tgl_masuk' => $karyawan->tgl_masuk,
            'jabatan' => $karyawan->jabatan,
        ];
    }
}
