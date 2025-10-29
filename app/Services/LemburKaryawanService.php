<?php

namespace App\Services;

use App\Repositories\LemburKaryawanRepository;
use App\Services\PayrollCalculationService;
use Illuminate\Pagination\LengthAwarePaginator;

class LemburKaryawanService
{
  public function __construct(
    private LemburKaryawanRepository $repository,
    private PayrollCalculationService $payrollService
  ) {}

  public function getAllLembur(array $filters = []): LengthAwarePaginator
  {
    return $this->repository->getAll($filters);
  }

  public function getLemburById(string $id): ?array
  {
    $lembur = $this->repository->findById($id);
    return $lembur ? $lembur->toArray() : null;
  }

  public function createLembur(array $data): array
  {
    // Process calculation dengan rumus lembur
    $processedData = $this->payrollService->processLemburCalculation($data);
    $lembur = $this->repository->create($processedData);
    return $lembur->toArray();
  }

  public function updateLembur(string $id, array $data): ?array
  {
    // Jika jam_lembur diupdate, recalculate
    if (isset($data['jam_lembur']) || isset($data['karyawan_id'])) {
      $existing = $this->repository->findById($id);
      if (!$existing) {
        return null;
      }
      $jamLembur = $data['jam_lembur'] ?? $existing->jam_lembur;
      $karyawanId = $data['karyawan_id'] ?? $existing->karyawan_id;
      $calculation = $this->payrollService->calculateRupiahLembur($karyawanId, $jamLembur);
      $data = array_merge($data, $calculation);
    }

    $updated = $this->repository->update($id, $data);

    if (!$updated) {
      return null;
    }
    return $this->repository->findById($id)->toArray();
  }

  public function deleteLembur(string $id): bool
  {
    return $this->repository->delete($id);
  }

  public function getLemburByKaryawan(string $karyawanId, array $filters = []): LengthAwarePaginator
  {
    return $this->repository->getByKaryawanId($karyawanId, $filters);
  }
}
