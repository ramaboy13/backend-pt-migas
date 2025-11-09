<?php

namespace App\Services;

use App\Repositories\GajiKaryawanRepository;
use App\Services\PayrollCalculationService;
use Illuminate\Pagination\LengthAwarePaginator;

class GajiKaryawanService
{
  public function __construct(
    private GajiKaryawanRepository $repository,
    private PayrollCalculationService $payrollService
  ) {}

  public function getAllGaji(array $filters = []): LengthAwarePaginator
  {
    return $this->repository->getAll($filters);
  }

  public function getGajiById(string $id): ?array
  {
    $gaji = $this->repository->findById($id);
    return $gaji ? $gaji->toArray() : null;
  }

  public function createGaji(array $data): array
  {
    // Cek apakah sudah ada gaji untuk karyawan di periode yang sama
    $existing = $this->repository->findByKaryawanAndPeriode($data['karyawan_id'], $data['periode']);
    if ($existing) {
      throw new \InvalidArgumentException('Gaji untuk karyawan pada periode ini sudah ada');
    }

    // Process calculation dengan rumus gaji
    $processedData = $this->payrollService->processGajiKaryawanCalculation($data);

    $gaji = $this->repository->create($processedData);
    return $gaji->toArray();
  }

  public function updateGaji(string $id, array $data): ?array
  {
    $existing = $this->repository->findById($id);
    if (!$existing) {
      return null;
    }

    // Jika ada perubahan yang mempengaruhi perhitungan
    $needsRecalculation = isset($data['pendapatan_id']) ||
      isset($data['potongan_id']) ||
      isset($data['pph21']);

    if ($needsRecalculation) {
      $pendapatanId = $data['pendapatan_id'] ?? $existing->pendapatan_id;
      $potonganId = $data['potongan_id'] ?? $existing->potongan_id;
      $pph21 = $data['pph21'] ?? $existing->pph21;

      // Cek unique constraint untuk update
      if (isset($data['karyawan_id']) || isset($data['periode'])) {
        $karyawanId = $data['karyawan_id'] ?? $existing->karyawan_id;
        $periode = $data['periode'] ?? $existing->periode;

        $duplicate = $this->repository->findByKaryawanAndPeriode($karyawanId, $periode);
        if ($duplicate && $duplicate->id !== $id) {
          throw new \InvalidArgumentException('Gaji untuk karyawan pada periode ini sudah ada');
        }
      }

      $calculation = $this->payrollService->calculateGajiKaryawan($pendapatanId, $potonganId, $pph21);
      $data = array_merge($data, [
        'subtotal' => $calculation['subtotal'],
        'gaji_bersih' => $calculation['gaji_bersih']
      ]);
    }

    $updated = $this->repository->update($id, $data);

    if (!$updated) {
      return null;
    }

    return $this->repository->findById($id)->toArray();
  }

  public function deleteGaji(string $id): bool
  {
    return $this->repository->delete($id);
  }

  public function getGajiByKaryawan(string $karyawanId, array $filters = []): LengthAwarePaginator
  {
    return $this->repository->getByKaryawanId($karyawanId, $filters);
  }


  public function getSummaryByPeriode(string $periode): array
  {
    return $this->repository->getSummaryByPeriode($periode);
  }

  /**
   * Recalculate gaji when periode changes
   */
  public function recalculateGaji(string $periode): void
  {
    $this->payrollService->recalculateGajiByPeriode($periode);
  }

  /**
   * Recalculate gaji when pendapatan changes
   */
  public function recalculateGajiByPendapatan(string $pendapatanId): void
  {
    $this->payrollService->recalculateGajiByPendapatan($pendapatanId);
  }

  /**
   * Recalculate gaji when potongan changes
   */
  public function recalculateGajiByPotongan(string $potonganId): void
  {
    $this->payrollService->recalculateGajiByPotongan($potonganId);
  }
}
