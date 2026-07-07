<?php

namespace App\Repositories;

use App\Models\KasPerusahaan;
use App\Models\SumberKas;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class KasPerusahaanRepository
{
    public function __construct(private KasPerusahaan $model) {}

    public function getAllPaginated(array $filters = [], int $perPage = 10, bool $withRelations = true): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Memuat relasi dengan sumber kas dan transaksi operasional
        if ($withRelations) {
            $query->with([
                'sumberKas',
                'transaksiOperasional' => function ($query) {
                    $query->withTrashed();
                },
            ]);
        }

        // Filter tanggal
        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']]);
        }

        if (! empty($filters['sumber_kas_id'])) {
            $query->where('sumber_kas_id', $filters['sumber_kas_id']);
        }

        if (! empty($filters['tipe_transaksi'])) {
            $query->where('tipe_transaksi', $filters['tipe_transaksi']);
        }

        // Filter multiple column
        if(!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                ->orWhere('tipe_transaksi', 'like', "%{$search}%");
            });
        }

        // Order by
        $query->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    public function findById(string $id, bool $withRelations = true): ?KasPerusahaan
    {
        $query = $this->model->newQuery();

        if ($withRelations) {
            $query->with(['sumberKas', 'transaksiOperasional']);
        }

        return $query->find($id);
    }

    public function create(array $data): KasPerusahaan
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $kasPerusahaan = $this->findById($id, false);

        if (! $kasPerusahaan) {
            return false;
        }

        return $kasPerusahaan->update($data);
    }

    public function delete(string $id): bool
    {
        $kasPerusahaan = $this->findById($id, false);

        if (! $kasPerusahaan) {
            return false;
        }

        return $kasPerusahaan->delete();
    }

    public function getAllAfterDateQuery(string $sumberKasId, string $tanggal, string $createdAt): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model
            ->where('sumber_kas_id', $sumberKasId)
            ->where(function ($query) use ($tanggal, $createdAt) {
                $query->where('tanggal', '>', $tanggal)
                    ->orWhere(function ($q) use ($tanggal, $createdAt) {
                        $q->where('tanggal', $tanggal)
                            ->where('created_at', '>=', $createdAt);
                    });
            })
            ->orderBy('tanggal', 'asc')
            ->orderBy('created_at', 'asc');
    }
}
