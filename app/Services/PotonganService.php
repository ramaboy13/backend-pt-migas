<?php

namespace App\Services;

use App\DTO\PotonganKaryawan\PotonganKaryawanDTO;
use App\DTO\PotonganKaryawan\PotonganKaryawanCollectionDTO;
use App\Repositories\PotonganRepository;
use App\Services\PayrollCalculationService;
use Illuminate\Pagination\LengthAwarePaginator;

class PotonganService
{
  public function __construct(
    private PotonganRepository $repository,
    private PayrollCalculationService $payrollService
  ) {}

  public function getAllPotongan(array $filters = []): PotonganKaryawanCollectionDTO
  {
    // Load karyawan relationship for DTO
    $filters['with_karyawan'] = true;
    $potongans = $this->repository->getAll($filters);

    return PotonganKaryawanCollectionDTO::fromPaginator($potongans);
  }

  public function getPotonganById(string $id): ?PotonganKaryawanDTO
  {
    $potongan = $this->repository->findById($id);

    if (!$potongan) {
      return null;
    }

    // Load karyawan relationship if not already loaded
    if (!$potongan->relationLoaded('karyawan')) {
      $potongan->load('karyawan');
    }

    return PotonganKaryawanDTO::fromModel($potongan);
  }

  public function createPotongan(array $data): PotonganKaryawanDTO
  {
    // Cek apakah sudah ada potongan untuk karyawan di periode yang sama
    $existing = $this->repository->findByKaryawanAndPeriode($data['karyawan_id'], $data['periode']);
    if ($existing) {
      throw new \InvalidArgumentException('Potongan untuk karyawan pada periode ini sudah ada');
    }

    // Process calculation dengan rumus potongan
    $processedData = $this->payrollService->processPotonganCalculation($data);

    $potongan = $this->repository->create($processedData);

    // Load karyawan relationship for DTO
    $potongan->load('karyawan');

    return PotonganKaryawanDTO::fromModel($potongan);
  }

  public function updatePotongan(string $id, array $data): ?PotonganKaryawanDTO
  {
    $existing = $this->repository->findById($id);
    if (!$existing) {
      return null;
    }

    // Jika ada perubahan yang mempengaruhi perhitungan
    $needsRecalculation = isset($data['karyawan_id']);

    if ($needsRecalculation) {
      $karyawanId = $data['karyawan_id'] ?? $existing->karyawan_id;
      $periode = $data['periode'] ?? $existing->periode;

      // Cek unique constraint untuk update
      if (isset($data['karyawan_id']) || isset($data['periode'])) {
        $duplicate = $this->repository->findByKaryawanAndPeriode($karyawanId, $periode);
        if ($duplicate && $duplicate->id !== $id) {
          throw new \InvalidArgumentException('Potongan untuk karyawan pada periode ini sudah ada');
        }
      }

      $calculation = $this->payrollService->calculatePotongan($karyawanId);
      $data = array_merge($data, [
        'rp_bpjs_kesehatan' => $calculation['rp_bpjs_kesehatan'],
        'rp_bpjs_tenagakerja' => $calculation['rp_bpjs_tenagakerja'],
        'total_potongan' => $calculation['total_potongan']
      ]);
    }

    $updated = $this->repository->update($id, $data);

    if (!$updated) {
      return null;
    }

    $potongan = $this->repository->findById($id);

    // Load karyawan relationship for DTO
    $potongan->load('karyawan');

    return PotonganKaryawanDTO::fromModel($potongan);
  }

  public function deletePotongan(string $id): bool
  {
    return $this->repository->delete($id);
  }

  public function getPotonganByKaryawan(string $karyawanId, array $filters = []): PotonganKaryawanCollectionDTO
  {
    // Load karyawan relationship for DTO
    $filters['with_karyawan'] = true;
    $potongans = $this->repository->getByKaryawanId($karyawanId, $filters);

    return PotonganKaryawanCollectionDTO::fromPaginator($potongans);
  }

  /**
   * Recalculate potongan when periode changes
   */
  public function recalculatePotongan(string $periode): void
  {
    $this->payrollService->recalculatePotonganByPeriode($periode);
  }

  /**
   * Recalculate potongan when karyawan data changes
   */
  public function recalculatePotonganByKaryawan(string $karyawanId): void
  {
    $this->payrollService->recalculatePotonganByKaryawan($karyawanId);
  }
}
