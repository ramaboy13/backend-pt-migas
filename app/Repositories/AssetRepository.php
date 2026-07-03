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

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // filter multiple collumn
        if(!empty($filters['search'])) {
           $search = $filters['search'];
           $query->where(function ($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('identitas', 'like', "%{$search}%")
              ->orWhere('catatan', 'like', "%{$search}%");
        });
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

    public function getForDropdown(): \Illuminate\Support\Collection
    {
        return $this->model->where('status', 'Tersedia')->pluck('nama', 'id');
    }
}
