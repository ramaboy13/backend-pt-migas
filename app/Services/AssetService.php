<?php

namespace App\Services;

use App\Repositories\AssetRepository;


class AssetService
{
    protected $assetRepository;

    public function __construct(AssetRepository $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    public function getAllAssets(array $filters = []): array
    {
        try {
            // Clean filters
            if (isset($filters['its_rfu'])) {
                $filters['its_rfu'] = filter_var($filters['its_rfu'], FILTER_VALIDATE_BOOLEAN);
            }

            $assets = $this->assetRepository->getAll($filters);
            
            // Simpel response
            $Response = [
                'items' => $assets->items(),
                'pagination' => [
                    'current_page' => $assets->currentPage(),
                    'per_page' => $assets->perPage(),
                    'total' => $assets->total(),
                    'total_pages' => $assets->lastPage(),
                    'has_more' => $assets->hasMorePages(),
                ]
            ];
            
            return [
                'success' => true,
                'data' => $Response,
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
            // Check if identity already exists
            if ($this->assetRepository->identityExists($data['identity'])) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Asset identity already exists'
                ];
            }

            // Panggil query create
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

            // Cek apakah identity sudah ada
            if (isset($data['identity']) && $this->assetRepository->identityExists($data['identity'], $id)) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Asset identity already exists'
                ];
            }

            // Panggil query update
            $updated = $this->assetRepository->update($id, $data);

            if (!$updated) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Failed to update asset'
                ];
            }

            // panggil asset baru
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
            // Check if asset exists
            $asset = $this->assetRepository->findById($id);
            if (!$asset) {
                return [
                    'success' => false,
                    'message' => 'Asset not found'
                ];
            }

            // Panggil query delete
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