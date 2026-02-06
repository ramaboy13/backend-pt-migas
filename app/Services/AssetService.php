<?php

namespace App\Services;

use App\Repositories\AssetRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class AssetService
{
    public function __construct(private AssetRepository $repository) {}

    public function getAllAssets(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        // Clean filters
        if (isset($filters['its_rfu'])) {
            $filters['its_rfu'] = filter_var($filters['its_rfu'], FILTER_VALIDATE_BOOLEAN);
        }
        return $this->repository->getAll($filters, $perPage);
    }

    public function getAssetById(string $id): ?array
    {
        $asset = $this->repository->findById($id);
        return $asset ? $asset->toArray() : null;
    }


    public function createAsset(array $data): array
    {
        $asset = $this->repository->create($data);
        return $asset->toArray();
    }


    public function updateAsset(string $id, array $data): ?array
    {
        $asset = $this->repository->findById($id);
        if (!$asset) {
            return null;
        }

        $updated = $this->repository->update($id, $data);
        if (!$updated) {
            return null;
        }

        $updatedAsset = $this->repository->findById($id);
        return $updatedAsset->toArray();
    }

    public function deleteAsset(string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getAllAssetsFullData(): array
    {
        return $this->repository->getAllAssetsFullData();
    }
}
