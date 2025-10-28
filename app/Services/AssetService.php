<?php

namespace App\Services;

use App\Repositories\AssetRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class AssetService
{
    public function __construct(private AssetRepository $assetRepository) {}

    public function getAllAssets(array $filters = []): array
    {
        try {
            // Clean filters
            if (isset($filters['its_rfu'])) {
                $filters['its_rfu'] = filter_var($filters['its_rfu'], FILTER_VALIDATE_BOOLEAN);
            }
            $assets = $this->assetRepository->getAll($filters);

            $responseData = [
                'items' => $assets->items(),
                'meta' => [
                    'current_page' => $assets->currentPage(),
                    'per_page' => $assets->perPage(),
                    'total' => $assets->total(),
                    'last_pages' => $assets->lastPage(),
                ]
            ];

            return [
                'success' => true,
                'data' => $responseData,
                'message' => 'Assets retrieved successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Failed to retrieve assets: ' . $e->getMessage()
            ];
        }
    }

    public function getAssetById(string $id): array
    {
        try {
            $asset = $this->assetRepository->findById($id);

            if (!$asset) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Asset not found'
                ];
            }

            return [
                'success' => true,
                'data' => $asset,
                'message' => 'Asset retrieved successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Failed to retrieve asset: ' . $e->getMessage()
            ];
        }
    }

    public function createAsset(array $data): array
    {
        try {

            $asset = $this->assetRepository->create($data);

            return [
                'success' => true,
                'data' => $asset,
                'message' => 'Asset created successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Failed to create asset: ' . $e->getMessage()
            ];
        }
    }

    public function updateAsset(string $id, array $data): array
    {
        try {
            // Cek apakah asset ada
            $asset = $this->assetRepository->findById($id);
            if (!$asset) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Asset not found'
                ];
            }

            $updated = $this->assetRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Failed to update asset'
                ];
            }

            $updatedAsset = $this->assetRepository->findById($id);

            return [
                'success' => true,
                'data' => $updatedAsset,
                'message' => 'Asset updated successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Failed to update asset: ' . $e->getMessage()
            ];
        }
    }

    public function deleteAsset(string $id): array
    {
        try {
            $asset = $this->assetRepository->findById($id);
            if (!$asset) {
                return [
                    'success' => false,
                    'message' => 'Asset not found'
                ];
            }

            $deleted = $this->assetRepository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Failed to delete asset'
                ];
            }

            return [
                'success' => true,
                'message' => 'Asset deleted successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete asset: ' . $e->getMessage()
            ];
        }
    }
}
