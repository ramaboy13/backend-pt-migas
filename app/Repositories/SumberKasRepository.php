<?php

// app/Repositories/SumberKasRepository.php

namespace App\Repositories;

use App\Models\SumberKas;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SumberKasRepository
{
    public function __construct(private SumberKas $model) {}

    public function getAllPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply filters
        if (! empty($filters['tipe'])) {
            $query->where('tipe', $filters['tipe']);
        }

        if (isset($filters['aktif'])) {
            $query->where('aktif', filter_var($filters['aktif'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Order by
        $query->orderBy('tipe', 'desc')
            ->orderBy('nama_bank', 'asc')
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    public function getAllActive(): Collection
    {
        return $this->model->active()->get();
    }

    public function getAllBanks(): Collection
    {
        return $this->model->bank()->active()->get();
    }

    public function getAllCash(): Collection
    {
        return $this->model->cash()->active()->get();
    }

    public function findById(string $id): ?SumberKas
    {
        return $this->model->find($id);
    }

    public function findByNomorRekening(string $nomorRekening): ?SumberKas
    {
        return $this->model->where('nomor_rekening', $nomorRekening)->first();
    }

    public function create(array $data): SumberKas
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $sumberKas = $this->findById($id);

        if (! $sumberKas) {
            return false;
        }

        return $sumberKas->update($data);
    }

    public function delete(string $id): bool
    {
        $sumberKas = $this->findById($id);

        if (! $sumberKas) {
            return false;
        }

        // Check if sumber kas has related transactions
        if ($sumberKas->kasPerusahaan()->exists()) {
            // Soft delete if has transactions
            return $sumberKas->delete();
        }

        // Permanent delete if no transactions
        return $sumberKas->forceDelete();
    }

    public function restore(string $id): bool
    {
        $sumberKas = $this->model->withTrashed()->find($id);

        if (! $sumberKas) {
            return false;
        }

        return $sumberKas->restore();
    }

    public function updateSaldo(string $id, float $saldoBaru): bool
    {
        $sumberKas = $this->findById($id);

        if (! $sumberKas) {
            return false;
        }

        return $sumberKas->updateSaldoTerakhir($saldoBaru);
    }

    public function getTotalSaldo(): float
    {
        return $this->model->active()->sum('saldo_terakhir');
    }

    public function getSaldoByTipe(string $tipe): float
    {
        return $this->model->where('tipe', $tipe)->active()->sum('saldo_terakhir');
    }
}
