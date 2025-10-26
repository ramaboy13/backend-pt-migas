<?php

namespace App\Repositories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AssetRepository
{
    protected $model;

    public function __construct(Asset $model)
    {
        $this->model = $model;
    }

    // Ambil semua asset dengan pagination
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Filter by RFU status
        if (isset($filters['its_rfu'])) {
            $query->where('its_rfu', $filters['its_rfu']);
        }

        // Search by name
        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    // Cari asset by ID
    public function findById(string $id): ?Asset
    {
        return $this->model->find($id);
    }

    // Buat asset baru
    public function create(array $data): Asset
    {
        return $this->model->create($data);
    }

    // Update asset
    public function update(string $id, array $data): bool
    {
        $asset = $this->findById($id);
        return $asset ? $asset->update($data) : false;
    }

    // Hapus asset
    public function delete(string $id): bool
    {
        $asset = $this->findById($id);
        return $asset ? $asset->delete() : false;
    }

    // Cek apakah identity sudah ada
    public function identityExists(string $identity, ?string $excludeId = null): bool
    {
        $query = $this->model->where('identity', $identity);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}