<?php

namespace App\Services;

use App\DTO\TransaksiOperasional\TransaksiOperasionalCollectionDTO;
use App\DTO\TransaksiOperasional\TransaksiOperasionalDTO;
use App\Repositories\TransaksiOperasionalRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransaksiOperasionalService
{
    public function __construct(
        private TransaksiOperasionalRepository $repository,
        private KasPerusahaanService $kasPerusahaanService
    ) {}

    public function getAllTransaksi(
        array $filters = [],
        int $perPage = 10
    ): TransaksiOperasionalCollectionDTO {
        $paginator = $this->repository->getAll($filters, $perPage);

        return TransaksiOperasionalCollectionDTO::fromPaginator($paginator);
    }

    public function getTransaksiById(string $id): ?TransaksiOperasionalDTO
    {
        $transaksi = $this->repository->findById($id);

        return $transaksi
            ? TransaksiOperasionalDTO::fromModel($transaksi)
            : null;
    }

    public function createTransaksi(array $data): TransaksiOperasionalDTO
    {
        return DB::transaction(function () use ($data) {
            $kasData = $this->prepareKasPerusahaanData($data);
            $kasPerusahaan = $this->kasPerusahaanService->createKasPerusahaan($kasData);

            $data['kas_perusahaan_id'] = $kasPerusahaan->id;

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

            $this->validateTransaksiData($data, $existing);

            if (
                $existing->kas_perusahaan_id &&
                (
                    isset($data['jumlah']) ||
                    isset($data['sumber_kas_id']) ||
                    isset($data['is_pemasukan'])
                )
            ) {
                $kasData = $this->prepareKasPerusahaanData(
                    array_merge($existing->toArray(), $data)
                );

                $this->kasPerusahaanService->updateKasPerusahaan(
                    $existing->kas_perusahaan_id,
                    $kasData
                );
            }

            if (! $this->repository->update($id, $data)) {
                return null;
            }

            return $this->getTransaksiById($id);
        });
    }

    public function deleteTransaksi(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $transaksi = $this->repository->findById($id, false);

            if (! $transaksi) {
                return false;
            }

            $kasPerusahaanId = $transaksi->kas_perusahaan_id;

            if (! $this->repository->delete($id)) {
                return false;
            }

            if ($kasPerusahaanId) {
                try {
                    $this->kasPerusahaanService->deleteKasPerusahaan($kasPerusahaanId);
                } catch (\Exception $e) {
                    Log::error(
                        "Failed to delete kas perusahaan {$kasPerusahaanId}: {$e->getMessage()}"
                    );
                }
            }

            return true;
        });
    }

    public function getSummaryByPeriode(
        string $startDate,
        string $endDate
    ): array {
        return $this->repository->getSummaryByPeriode($startDate, $endDate);
    }

    public function getByPangkalan(
        string $pangkalanId,
        array $filters = []
    ): TransaksiOperasionalCollectionDTO {
        $paginator = $this->repository->getByPangkalan($pangkalanId, $filters);

        return TransaksiOperasionalCollectionDTO::fromPaginator($paginator);
    }

    private function validateTransaksiData(
        array $data,
        ?\App\Models\TransaksiOperasional $existing = null
    ): void {
        if (isset($data['jumlah']) && $data['jumlah'] <= 0) {
            throw new \InvalidArgumentException(
                'Jumlah transaksi harus lebih dari 0'
            );
        }

        $jenisTransaksi = $data['jenis_transaksi']
            ?? $existing?->jenis_transaksi;

        match ($jenisTransaksi) {
            'PENJUALAN_GAS' => $this->validatePenjualanGas($data, $existing),
            'PEMBELIAN_GAS' => $this->validatePembelianGas($data, $existing),
            'MAINTENANCE',
            'LAINNYA' => null,
            default => null,
        };
    }

    private function validatePenjualanGas(
        array $data,
        ?\App\Models\TransaksiOperasional $existing
    ): void {
        if (empty($data['pangkalan_id']) && empty($existing?->pangkalan_id)) {
            throw new \InvalidArgumentException(
                'Pangkalan harus dipilih untuk penjualan ke pangkalan'
            );
        }

        if (empty($data['tabung_id']) && empty($existing?->tabung_id)) {
            throw new \InvalidArgumentException(
                'Tabung harus dipilih untuk transaksi penjualan gas'
            );
        }

        if (
            (empty($data['qty']) && empty($existing?->qty)) ||
            (isset($data['qty']) && $data['qty'] <= 0)
        ) {
            throw new \InvalidArgumentException(
                'Quantity harus lebih dari 0'
            );
        }
    }

    private function validatePembelianGas(
        array $data,
        ?\App\Models\TransaksiOperasional $existing
    ): void {
        if (empty($data['tabung_id']) && empty($existing?->tabung_id)) {
            throw new \InvalidArgumentException(
                'Tabung harus dipilih untuk transaksi pembelian gas'
            );
        }

        if (
            (empty($data['qty']) && empty($existing?->qty)) ||
            (isset($data['qty']) && $data['qty'] <= 0)
        ) {
            throw new \InvalidArgumentException(
                'Quantity harus lebih dari 0'
            );
        }
    }

    private function prepareKasPerusahaanData(array $transaksiData): array
    {
        $jumlah = $transaksiData['jumlah'] ?? 0;
        $isPemasukan = $transaksiData['is_pemasukan'] ?? false;
        $sumberKasId = $transaksiData['sumber_kas_id'] ?? null;

        if (! $sumberKasId) {
            throw new \InvalidArgumentException('Sumber kas harus dipilih');
        }

        return [
            'tanggal' => $transaksiData['tanggal'] ?? now()->toDateString(),
            'sumber_kas_id' => $sumberKasId,
            'keterangan' => $transaksiData['keterangan'] ?? 'Transaksi Operasional',
            'tipe_transaksi' => $isPemasukan ? 'DEBIT' : 'KREDIT',
            'jumlah' => $jumlah,
            'created_by' => Auth::user()->name ?? 'system',
        ];
    }

    }
