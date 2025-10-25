<?php
// app/Repositories/KasBcaRepository.php

namespace App\Repositories;

use App\Models\KasBca;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class KasBcaRepository
{
    public function __construct(private KasBca $model) {}

    public function findById(string $id): ?array
    {
        $kasBca = $this->model->find($id);
        
        if (!$kasBca) {
            return null;
        }
        
        return $this->formatItem($kasBca);
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->orderBy('tanggal', 'desc')
            ->paginate($perPage)
            ->through(function ($item) {
                return $this->formatItem($item);
            });
    }

    // public function getByDateRange(string $startDate, string $endDate): Collection
    // {
    //     return $this->model
    //         ->whereDate('tanggal', '>=', $startDate)
    //         ->whereDate('tanggal', '<=', $endDate)
    //         ->orderBy('tanggal', 'desc')
    //         ->get()
    //         ->map(function ($item) {
    //             return $this->formatItem($item);
    //         });
    // }

    public function create(array $data): array
    {
        $kasBca = $this->model->create($data);
        return $this->formatItem($kasBca);
    }

    public function update(string $id, array $data): ?array
    {
        $kasBca = $this->model->find($id);
        
        if (!$kasBca) {
            return null;
        }
        
        $kasBca->update($data);
        return $this->formatItem($kasBca->fresh());
    }

    public function delete(string $id): bool
    {
        $kasBca = $this->model->find($id);
        
        if (!$kasBca) {
            return false;
        }
        
        return $kasBca->delete();
    }

    private function formatItem(KasBca $kasBca): array
    {
        return [
            'id' => $kasBca->id,
            'tanggal' => $kasBca->tanggal->toDateString(),
            'keterangan' => $kasBca->keterangan,
            'debit' => (float) $kasBca->debit,
            'kredit' => (float) $kasBca->kredit,
            'saldo' => (float) $kasBca->saldo,
            'saldo_akhir' => (float) $kasBca->saldo_akhir,
            'created_at' => $kasBca->created_at->toISOString(),
            'updated_at' => $kasBca->updated_at->toISOString(),
        ];
    }
}