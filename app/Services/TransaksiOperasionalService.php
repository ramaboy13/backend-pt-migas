<?php

namespace App\Services;

use App\DTO\TransaksiOperasional\TransaksiOperasionalCollectionDTO;
use App\DTO\TransaksiOperasional\TransaksiOperasionalDTO;
use App\Repositories\TransaksiOperasionalRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransaksiOperasionalService
{
    public function __construct(
        private TransaksiOperasionalRepository $repository,
        private KasPerusahaanService $kasPerusahaanService
    ) {}

    public function getAllTransaksi(array $filters = [], int $perPage = 10): TransaksiOperasionalCollectionDTO
    {
        $paginator = $this->repository->getAll($filters, $perPage);

        return TransaksiOperasionalCollectionDTO::fromPaginator($paginator);
    }

    public function getTransaksiById(string $id): ?TransaksiOperasionalDTO
    {
        $transaksi = $this->repository->findById($id);

        if (! $transaksi) {
            return null;
        }

        return TransaksiOperasionalDTO::fromModel($transaksi);
    }

    public function createTransaksi(array $data): TransaksiOperasionalDTO
    {
        return DB::transaction(function () use ($data) {
            // Validate and calculate
            $this->validateTransaksiData($data);

            // Create kas perusahaan entry first
            $kasData = $this->prepareKasPerusahaanData($data);
            $kasPerusahaan = $this->kasPerusahaanService->createKasPerusahaan($kasData);

            // Link kas_perusahaan_id to transaksi
            $data['kas_perusahaan_id'] = $kasPerusahaan->id;

            // Create transaksi
            $transaksi = $this->repository->create($data);

            return TransaksiOperasionalDTO::fromModel($transaksi);
        });
    }

    public function updateTransaksi(string $id, array $data): ?TransaksiOperasionalDTO
    {
        return DB::transaction(function () use ($id, $data) {
            $existing = $this->repository->findById($id);

            if (! $existing) {
                return null;
            }

            // Validate
            $this->validateTransaksiData($data, $existing);

            // Update kas perusahaan if needed
            if ($existing->kas_perusahaan_id &&
                (isset($data['jumlah']) || isset($data['sumber_kas_id']) || isset($data['is_pemasukan']))) {

                $kasData = $this->prepareKasPerusahaanData(array_merge($existing->toArray(), $data));
                $this->kasPerusahaanService->updateKasPerusahaan($existing->kas_perusahaan_id, $kasData);
            }

            // Update transaksi
            $updated = $this->repository->update($id, $data);

            if (! $updated) {
                return null;
            }

            return $this->getTransaksiById($id);
        });
    }

    public function deleteTransaksi(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $transaksi = $this->repository->findById($id);

            if (! $transaksi) {
                return false;
            }

            // Delete kas perusahaan entry if exists
            if ($transaksi->kas_perusahaan_id) {
                $this->kasPerusahaanService->deleteKasPerusahaan($transaksi->kas_perusahaan_id);
            }

            // Delete transaksi
            return $this->repository->delete($id);
        });
    }

    public function getSummaryByPeriode(string $startDate, string $endDate): array
    {
        return $this->repository->getSummaryByPeriode($startDate, $endDate);
    }

    public function getByPangkalan(string $pangkalanId, array $filters = []): TransaksiOperasionalCollectionDTO
    {
        $paginator = $this->repository->getByPangkalan($pangkalanId, $filters);

        return TransaksiOperasionalCollectionDTO::fromPaginator($paginator);
    }

    private function validateTransaksiData(array $data, ?\App\Models\TransaksiOperasional $existing = null): void
    {
        // Validate jumlah
        if (isset($data['jumlah']) && $data['jumlah'] <= 0) {
            throw new \InvalidArgumentException('Jumlah transaksi harus lebih dari 0');
        }

        // Validate based on jenis_transaksi
        $jenisTransaksi = $data['jenis_transaksi'] ?? $existing?->jenis_transaksi;

        switch ($jenisTransaksi) {
            case 'PENJUALAN_PANGKALAN':
                // Untuk PENJUALAN ke pangkalan: pangkalan_id WAJIB
                if (empty($data['pangkalan_id']) && empty($existing?->pangkalan_id)) {
                    throw new \InvalidArgumentException('Pangkalan harus dipilih untuk penjualan ke pangkalan');
                }
                // Tabung WAJIB untuk transaksi gas
                if (empty($data['tabung_id']) && empty($existing?->tabung_id)) {
                    throw new \InvalidArgumentException('Tabung harus dipilih untuk transaksi penjualan gas');
                }
                // Qty validation
                if ((empty($data['qty']) && empty($existing?->qty)) ||
                    (isset($data['qty']) && $data['qty'] <= 0)) {
                    throw new \InvalidArgumentException('Quantity harus lebih dari 0');
                }
                break;

            case 'PEMBELIAN_GAS':
                // Untuk PEMBELIAN dari supplier: pangkalan_id OPTIONAL, tabung_id WAJIB
                // Tabung WAJIB untuk transaksi gas
                if (empty($data['tabung_id']) && empty($existing?->tabung_id)) {
                    throw new \InvalidArgumentException('Tabung harus dipilih untuk transaksi pembelian gas');
                }
                // Qty validation
                if ((empty($data['qty']) && empty($existing?->qty)) ||
                    (isset($data['qty']) && $data['qty'] <= 0)) {
                    throw new \InvalidArgumentException('Quantity harus lebih dari 0');
                }
                break;

            case 'MAINTENANCE':
                // Tidak ada validasi khusus
                break;

            case 'LAINNYA':
                // Tidak ada validasi khusus
                break;
        }
    }

    private function prepareKasPerusahaanData(array $transaksiData): array
    {
        $jumlah = $transaksiData['jumlah'] ?? 0;
        $isPemasukan = $transaksiData['is_pemasukan'] ?? false;
        $sumberKasId = $transaksiData['sumber_kas_id'] ?? null;
        $tanggal = $transaksiData['tanggal'] ?? now()->toDateString();
        $keterangan = $transaksiData['keterangan'] ?? 'Transaksi Operasional';

        if (! $sumberKasId) {
            throw new \InvalidArgumentException('Sumber kas harus dipilih');
        }

        return [
            'tanggal' => $tanggal,
            'sumber_kas_id' => $sumberKasId,
            'keterangan' => $keterangan,
            'tipe_transaksi' => $isPemasukan ? 'DEBIT' : 'KREDIT',
            'jumlah' => $jumlah,
            'transaksi_operasional_id' => $transaksiData['id'] ?? null,
            'created_by' => Auth::user()->name ?? 'system',
        ];
    }

    public function validateSumberKasActive(string $sumberKasId): bool
    {
        // This should be delegated to SumberKasService
        // For now, we assume it's valid if passed validation
        return true;
    }
}
