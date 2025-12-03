<?php
// app/Services/TransaksiOperasionalService.php

namespace App\Services;

use App\Models\TransaksiOperasional;
use App\Repositories\TransaksiOperasionalRepository;
use App\Services\PayrollCalculationService;
use App\DTO\TransaksiOperasional\TransaksiOperasionalDTO;
use App\DTO\TransaksiOperasional\TransaksiOperasionalCollectionDTO;

class TransaksiOperasionalService
{
    public function __construct(
        private TransaksiOperasionalRepository $repository,
        private PayrollCalculationService $payrollService
    ) {}

    public function getAllTransaksi(array $filters = [], int $perPage = 10): TransaksiOperasionalCollectionDTO
    {
        $result = $this->repository->getAll($filters, $perPage);
        return TransaksiOperasionalCollectionDTO::fromPaginator($result);
    }

    public function getTransaksiById(string $id): ?TransaksiOperasionalDTO
    {
        $transaksi = $this->repository->findById($id);
        return $transaksi ? TransaksiOperasionalDTO::fromModel($transaksi) : null;
    }

   public function createTransaksi(array $data): TransaksiOperasionalDTO
    {
        $processedData = $this->payrollService->processTransaksiCalculation($data);
        $transaksi = $this->repository->create($processedData);
        return TransaksiOperasionalDTO::fromModel($transaksi);
    }

    public function updateTransaksi(string $id, array $data): ?TransaksiOperasionalDTO
    {
        $existing = $this->repository->findById($id);
        if (!$existing) {
            return null;
        }
        $needsRecalculation = isset($data['pangkalan_id']) || 
                             isset($data['qty']) || 
                             isset($data['is_in']);

        if ($needsRecalculation) {
            // Merge dengan data existing untuk kalkulasi
            $calculationData = array_merge($existing->toArray(), $data);
            $calculationData['pangkalan_id'] = $data['pangkalan_id'] ?? $existing->pangkalan_id;
            $calculationData['qty'] = $data['qty'] ?? $existing->qty;
            $calculationData['is_in'] = $data['is_in'] ?? $existing->is_in;
            
            $calculation = $this->payrollService->calculateTransaksiOperasional($calculationData);
            $data = array_merge($data, $calculation);
        }

        $updated = $this->repository->update($id, $data);

        if (!$updated) {
            return null;
        }

        $updatedTransaksi = $this->repository->findById($id);
        return $updatedTransaksi ? TransaksiOperasionalDTO::fromModel($updatedTransaksi) : null;
    }
    public function deleteTransaksi(string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getByPangkalan(string $pangkalanId, array $filters = []): TransaksiOperasionalCollectionDTO
    {
        $result = $this->repository->getByPangkalan($pangkalanId, $filters);
        return TransaksiOperasionalCollectionDTO::fromPaginator($result);
    }

    public function getByTabung(string $tabungId, array $filters = []): TransaksiOperasionalCollectionDTO
    {
        $result = $this->repository->getByTabung($tabungId, $filters);
        return TransaksiOperasionalCollectionDTO::fromPaginator($result);
    }

    /**
     * Get summary by periode
     */
    public function getSummaryByPeriode(string $startDate, string $endDate): array
    {
        $transactions = $this->repository->getAll([
            'start_date' => $startDate,
            'end_date' => $endDate
        ], 1000);

        $totalDebit = 0;
        $totalCredit = 0;
        $totalTransaksi = 0;

        foreach ($transactions as $transaction) {
            if ($transaction instanceof TransaksiOperasional) {
                $totalDebit += $transaction->debit;
                $totalCredit += $transaction->credit;
                $totalTransaksi += $transaction->total;
            }
        }

        return [
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'total_transaksi' => round($totalTransaksi, 2),
            'net_balance' => round($totalDebit - $totalCredit, 2),
            'transaction_count' => $transactions->count()
        ];
    }

    /**
     * Recalculate transaksi when pangkalan harga_satuan changes
     */
    public function recalculateTransaksi(string $pangkalanId): void
    {
        $this->payrollService->recalculateTransaksiByPangkalan($pangkalanId);
    }
}