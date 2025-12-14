<?php

namespace App\Repositories;

use App\Models\KasPerusahaan;
use App\Models\SumberKas;
use Illuminate\Pagination\LengthAwarePaginator;

class KasPerusahaanRepository
{
    public function __construct(private KasPerusahaan $model) {}

    public function getAllPaginated(array $filters = [], int $perPage = 10, bool $withRelations = true): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Load relations if needed
        if ($withRelations) {
            $query->with(['sumberKas', 'transaksiOperasional']);
        }

        // Apply filters
        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']]);
        }

        if (! empty($filters['sumber_kas_id'])) {
            $query->where('sumber_kas_id', $filters['sumber_kas_id']);
        }

        if (! empty($filters['tipe_transaksi'])) {
            $query->where('tipe_transaksi', $filters['tipe_transaksi']);
        }

        if (! empty($filters['keterangan'])) {
            $query->where('keterangan', 'LIKE', '%'.$filters['keterangan'].'%');
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

    public function findByTransaksiOperasionalId(string $transaksiOperasionalId): ?KasPerusahaan
    {
        return $this->model->where('transaksi_operasional_id', $transaksiOperasionalId)->first();
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

    public function getSaldoSebelum(string $sumberKasId, string $tanggal): float
    {
        // Get last saldo_sesudah before the given date for this sumber_kas
        $lastRecord = $this->model
            ->where('sumber_kas_id', $sumberKasId)
            ->whereDate('tanggal', '<', $tanggal)
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastRecord) {
            return (float) $lastRecord->saldo_sesudah;
        }

        // If no previous record, get saldo_terakhir from sumber_kas
        $sumberKas = SumberKas::find($sumberKasId);

        return $sumberKas ? (float) $sumberKas->saldo_terakhir : 0;
    }

    public function updateSaldoTerakhirSumberKas(string $sumberKasId, float $saldoBaru): bool
    {
        return SumberKas::where('id', $sumberKasId)
            ->update(['saldo_terakhir' => $saldoBaru]);
    }
}
