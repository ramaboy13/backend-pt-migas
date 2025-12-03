<?php

namespace App\Repositories;

use App\Models\Asset;
use Illuminate\Pagination\LengthAwarePaginator;

class AssetRepository
{
    public function __construct(private Asset $model) {}

    public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Filter by RFU status
        if (isset($filters['its_rfu'])) {
            $query->where('its_rfu', $filters['its_rfu']);
        }

        // Search by name
        if (isset($filters['search'])) {
            $query->where('nama', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(string $id): ?Asset
    {
        return $this->model->find($id);
    }

    public function create(array $data): Asset
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $asset = $this->findById($id);
        return $asset ? $asset->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $asset = $this->findById($id);
        return $asset ? $asset->delete() : false;
    }
}
