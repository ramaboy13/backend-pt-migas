<?php

namespace App\Repositories;

use App\Models\TransaksiOperasional;
use Illuminate\Pagination\LengthAwarePaginator;

class TransaksiOperasionalRepository
{
    public function __construct(private TransaksiOperasional $model) {}

    public function getAll(array $filters = [], int $perPage = 10)
    {
        $query = $this->model->with(['pangkalan', 'tabung', 'asset']);

        if (! empty($filters['tanggal'])) {
            $query->whereDate('tanggal', $filters['tanggal']);
        }

        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('tanggal', [
                $filters['start_date'],
                $filters['end_date'],
            ]);
        }

        if (! empty($filters['pangkalan_id'])) {
            $query->where('pangkalan_id', $filters['pangkalan_id']);
        }

        if (! empty($filters['tabung_id'])) {
            $query->where('tabung_id', $filters['tabung_id']);
        }

        if (isset($filters['is_pemasukan']) && $filters['is_pemasukan'] !== '') {
            $query->where('is_pemasukan', (bool) $filters['is_pemasukan']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                    ->orWhere('no_ref', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(string $id, bool $withRelations = true): ?TransaksiOperasional
    {
        $query = $this->model->newQuery();

        if ($withRelations) {
            $query->with(['pangkalan', 'tabung', 'asset', 'kasPerusahaan.sumberKas']);
        }

        return $query->find($id);
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
        $baseQuery = $this->model->whereBetween('tanggal', [$startDate, $endDate]);

        $totalPemasukan = (clone $baseQuery)->where('is_pemasukan', true)->sum('jumlah');
        $totalPengeluaran = (clone $baseQuery)->where('is_pemasukan', false)->sum('jumlah');
        $netBalance = $totalPemasukan - $totalPengeluaran;

        $summaryByJenis = (clone $baseQuery)
            ->selectRaw('jenis_transaksi, count(*) as count, sum(jumlah) as total')
            ->groupBy('jenis_transaksi')
            ->get()
            ->keyBy('jenis_transaksi')
            ->map(function ($item) {
                return [
                    'count' => $item->count,
                    'total' => (float) $item->total,
                ];
            })->toArray();

        return [
            'total_transaksi' => (clone $baseQuery)->count(),
            'total_pemasukan' => (float) $totalPemasukan,
            'total_pengeluaran' => (float) $totalPengeluaran,
            'net_balance' => (float) $netBalance,
            'summary_by_jenis' => $summaryByJenis,
        ];
    }

    }
