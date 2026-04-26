<?php

// app/Repositories/GajiKaryawanRepository.php

namespace App\Repositories;

use App\Models\GajiKaryawan;
use App\Models\Karyawan;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class GajiKaryawanRepository
{
    public function __construct(
        private GajiKaryawan $model,
        private Karyawan $karyawanModel
    ) {}

    public function getAll(array $filters = [], int $perPage = 10, bool $withRelations = true): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Load relations
        if ($withRelations) {
            $query->with(['karyawan']);
        }

        // Filter by periode (bulan + tahun)
        if (! empty($filters['periode'])) {
            $query->where('bulan', $filters['periode']['bulan'])
                ->where('tahun', $filters['periode']['tahun']);
        }

        // Filter by range tanggal gaji
        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('tanggal_gaji', [$filters['start_date'], $filters['end_date']]);
        }

        // Filter by karyawan_id
        if (! empty($filters['karyawan_id'])) {
            $query->where('karyawan_id', $filters['karyawan_id']);
        }

        // Filter by status
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by status aktif karyawan
        if (isset($filters['karyawan_aktif'])) {
            $query->whereHas('karyawan', function ($q) use ($filters) {
                $q->where('aktif', filter_var($filters['karyawan_aktif'], FILTER_VALIDATE_BOOLEAN));
            });
        }

        // Search by karyawan name or NIK
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('karyawan', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('NIK', 'like', "%{$search}%");
            });
        }

        // Order by
        $query->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    public function findById(string $id, bool $withRelations = true): ?GajiKaryawan
    {
        $query = $this->model->newQuery();

        if ($withRelations) {
            $query->with(['karyawan', 'komponenGajiLembur', 'komponenGajiTunjangan']);
        }

        return $query->find($id);
    }

    public function findByKaryawanAndPeriode(string $karyawanId, int $bulan, int $tahun): ?GajiKaryawan
    {
        return $this->model->where('karyawan_id', $karyawanId)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();
    }

    public function create(array $data): GajiKaryawan
    {
        // Validasi karyawan aktif
        $karyawan = $this->karyawanModel->find($data['karyawan_id']);

        if (! $karyawan) {
            throw new ModelNotFoundException('Karyawan tidak ditemukan');
        }

        if (! $karyawan->aktif) {
            throw new \InvalidArgumentException(
                'Tidak dapat membuat gaji untuk karyawan yang tidak aktif. '.
                "Karyawan {$karyawan->nama} (NIK: {$karyawan->NIK}) berstatus tidak aktif."
            );
        }

        // Validasi periode tidak sebelum tanggal masuk
        if ($karyawan->tgl_masuk) {
            $tanggalGaji = \Carbon\Carbon::create($data['tahun'], $data['bulan'], 1);
            $tanggalMasuk = \Carbon\Carbon::parse($karyawan->tgl_masuk);

            if ($tanggalGaji->lt($tanggalMasuk->startOfMonth())) {
                throw new \InvalidArgumentException(
                    'Periode gaji tidak boleh sebelum tanggal masuk karyawan. '.
                    "Tanggal masuk: {$karyawan->tgl_masuk->format('Y-m-d')}"
                );
            }
        }

        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $gaji = $this->findById($id, false);

        if (! $gaji) {
            throw new ModelNotFoundException("Gaji karyawan dengan ID {$id} tidak ditemukan");
        }

        return $gaji->update($data);
    }

    public function delete(string $id): bool
    {
        $gaji = $this->findById($id, false);

        if (! $gaji) {
            return false;
        }

        return $gaji->delete();
    }

    public function getByKaryawanId(string $karyawanId, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->where('karyawan_id', $karyawanId);

        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('tanggal_gaji', [$filters['start_date'], $filters['end_date']]);
        }

        if (! empty($filters['tahun'])) {
            $query->where('tahun', $filters['tahun']);
        }

        return $query->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function getSummaryByPeriode(int $bulan, int $tahun): array
    {
        $summary = $this->model->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->selectRaw('
                COUNT(*) as total_karyawan,
                SUM(gapok) as total_gapok,
                SUM(total_lembur) as total_lembur,
                SUM(total_tunjangan) as total_tunjangan,
                SUM(total_pendapatan) as total_pendapatan,
                SUM(total_potongan) as total_potongan,
                SUM(pph21) as total_pph21,
                SUM(gaji_bersih) as total_gaji_bersih
            ')
            ->first();

        return [
            'total_karyawan' => (int) ($summary->total_karyawan ?? 0),
            'total_gapok' => (float) ($summary->total_gapok ?? 0),
            'total_lembur' => (float) ($summary->total_lembur ?? 0),
            'total_tunjangan' => (float) ($summary->total_tunjangan ?? 0),
            'total_pendapatan' => (float) ($summary->total_pendapatan ?? 0),
            'total_potongan' => (float) ($summary->total_potongan ?? 0),
            'total_pph21' => (float) ($summary->total_pph21 ?? 0),
            'total_gaji_bersih' => (float) ($summary->total_gaji_bersih ?? 0),
        ];
    }

    public function getGajiBelumDiprosesByPeriode(int $bulan, int $tahun): LengthAwarePaginator
    {
        return $this->model->with('karyawan')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('status', 'DRAFT')
            ->paginate(10);
    }

    /**
     * Get all gaji for PDF export
     */
    public function getAllForPdf(array $filters = [])
    {
        $query = $this->model->with(['karyawan']);

        // Filter by periode (bulan + tahun)
        if (! empty($filters['bulan']) && ! empty($filters['tahun'])) {
            $query->where('bulan', $filters['bulan'])
                ->where('tahun', $filters['tahun']);
        }

        // Filter by range bulan/tahun
        if (! empty($filters['start_bulan']) && ! empty($filters['start_tahun']) &&
            ! empty($filters['end_bulan']) && ! empty($filters['end_tahun'])) {

            $startDate = Carbon::create($filters['start_tahun'], $filters['start_bulan'], 1);
            $endDate = Carbon::create($filters['end_tahun'], $filters['end_bulan'], 1)->endOfMonth();

            $query->whereBetween('tanggal_gaji', [$startDate, $endDate]);
        }

        // Filter by karyawan_id
        if (! empty($filters['karyawan_id'])) {
            $query->where('karyawan_id', $filters['karyawan_id']);
        }

        return $query->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->orderBy('tanggal_gaji', 'desc')
            ->get();
    }
}
