<?php

namespace App\Services;

use App\Models\TransaksiOperasional;
use App\Repositories\TransaksiOperasionalRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class TransaksiOperasionalService
{
  public function __construct(
    private TransaksiOperasionalRepository $repository
  ) {}

  public function getAllTransaksi(array $filters = []): LengthAwarePaginator
  {
    return $this->repository->getAll($filters);
  }

  public function getTransaksiById(string $id): ?array
  {
    $transaksi = $this->repository->findById($id);
    return $transaksi ? $transaksi->toArray() : null;
  }

  public function createTransaksi(array $data): array
  {
    // Auto-calculate debit/credit based on is_in
    $data = $this->calculateDebitCredit($data);

    $transaksi = $this->repository->create($data);
    return $transaksi->toArray();
  }

  public function updateTransaksi(string $id, array $data): ?array
  {
    $existing = $this->repository->findById($id);
    if (!$existing) {
      return null;
    }

    // Jika ada perubahan yang mempengaruhi perhitungan
    if (isset($data['is_in']) || isset($data['qty']) || isset($data['harga_satuan'])) {
      $data = $this->calculateDebitCredit($data, $existing);
    }

    $updated = $this->repository->update($id, $data);

    if (!$updated) {
      return null;
    }

    return $this->repository->findById($id)->toArray();
  }

  public function deleteTransaksi(string $id): bool
  {
    return $this->repository->delete($id);
  }


  /**
   * Calculate debit and credit based on is_in flag
   */
  private function calculateDebitCredit(array $data, ?TransaksiOperasional $existing = null): array
  {
    $isIn = $data['is_in'] ?? ($existing->is_in ?? true);
    $qty = $data['qty'] ?? ($existing->qty ?? 0);
    $hargaSatuan = $data['harga_satuan'] ?? ($existing->harga_satuan ?? 0);

    $total = $qty * $hargaSatuan;

    if ($isIn) {
      $data['debit'] = $total;
      $data['credit'] = 0;
    } else {
      $data['debit'] = 0;
      $data['credit'] = $total;
    }

    return $data;
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

    foreach ($transactions as $transaction) {
      if ($transaction instanceof TransaksiOperasional) {
        $totalDebit += $transaction->debit;
        $totalCredit += $transaction->credit;
      }
    }

    return [
      'total_debit' => $totalDebit,
      'total_credit' => $totalCredit,
      'net_balance' => $totalDebit - $totalCredit,
      'total_transactions' => $transactions->count()
    ];
  }
}
