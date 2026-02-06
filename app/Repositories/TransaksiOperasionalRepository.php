<?php

namespace App\Repositories;

use App\Models\TransaksiOperasional;
use Illuminate\Pagination\LengthAwarePaginator;

class TransaksiOperasionalRepository
{
    public function __construct(private TransaksiOperasional $model) {}

    public function getAll(array $filters = [], int $perPage = 10, bool $withRelations = true): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Load relations if needed
        if ($withRelations) {
            $query->with(['pangkalan', 'tabung', 'asset', 'kasPerusahaan.sumberKas']);
        }

        // Apply filters
        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']]);
        }

        if (! empty($filters['jenis_transaksi'])) {
            $query->where('jenis_transaksi', $filters['jenis_transaksi']);
        }

        if (isset($filters['is_pemasukan'])) {
            $query->where('is_pemasukan', filter_var($filters['is_pemasukan'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['pangkalan_id'])) {
            $query->where('pangkalan_id', $filters['pangkalan_id']);
        }

        if (! empty($filters['tabung_id'])) {
            $query->where('tabung_id', $filters['tabung_id']);
        }

        if (! empty($filters['asset_id'])) {
            $query->where('asset_id', $filters['asset_id']);
        }

        // filter multiple collumn
        if(!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('no_ref', 'like', "%{$search}%")
                ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        // Order by
        $query->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    public function findById(string $id, bool $withRelations = true): ?TransaksiOperasional
    {
        $query = $this->model->newQuery();

        if ($withRelations) {
            $query->with(['pangkalan', 'tabung', 'asset', 'kasPerusahaan.sumberKas']);
        }

        return $query->find($id);
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
        $transaksi = $this->findById($id, false);

        if (! $transaksi) {
            return false;
        }

        return $transaksi->update($data);
    }

    public function delete(string $id): bool
    {
        $transaksi = $this->findById($id, false);

        if (! $transaksi) {
            return false;
        }

        return $transaksi->delete();
    }

    public function getByPangkalan(string $pangkalanId, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['tabung', 'kasPerusahaan.sumberKas'])
            ->where('pangkalan_id', $pangkalanId);

        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']]);
        }

        if (! empty($filters['jenis_transaksi'])) {
            $query->where('jenis_transaksi', $filters['jenis_transaksi']);
        }

        return $query->orderBy('tanggal', 'desc')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function getSummaryByPeriode(string $startDate, string $endDate): array
    {
        $transactions = $this->model->whereBetween('tanggal', [$startDate, $endDate])->get();

        $totalPemasukan = $transactions->where('is_pemasukan', true)->sum('jumlah');
        $totalPengeluaran = $transactions->where('is_pemasukan', false)->sum('jumlah');
        $netBalance = $totalPemasukan - $totalPengeluaran;

        return [
            'total_transaksi' => $transactions->count(),
            'total_pemasukan' => (float) $totalPemasukan,
            'total_pengeluaran' => (float) $totalPengeluaran,
            'net_balance' => (float) $netBalance,
            'summary_by_jenis' => $transactions->groupBy('jenis_transaksi')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => (float) $group->sum('jumlah'),
                ];
            })->toArray(),
        ];
    }

    public function getByKasPerusahaan(string $kasPerusahaanId): ?TransaksiOperasional
    {
        return $this->model->where('kas_perusahaan_id', $kasPerusahaanId)->first();
    }
}
