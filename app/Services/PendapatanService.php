<?php

namespace App\Services;

use App\Repositories\PendapatanRepository;
use App\Services\PayrollCalculationService;
use Illuminate\Pagination\LengthAwarePaginator;

class PendapatanService
{
  public function __construct(
    private PendapatanRepository $repository,
    private PayrollCalculationService $payrollService
  ) {}

  public function getAllPendapatan(array $filters = []): LengthAwarePaginator
  {
    $result = $this->repository->getAll($filters);

    $result->getCollection()->transform(function ($pendapatan) {
      $data = $pendapatan->toArray();
      $lemburDetail = $this->payrollService->calculateTotalPendapatan(
        $pendapatan->karyawan_id,
        $pendapatan->periode,
        $pendapatan->tunjangan
      );

      return array_merge($data, [
        'total_lembur_perperiode' => $lemburDetail['total_lembur_perperiode'],
        'total_pendapatan_lembur_perperiode' => $lemburDetail['total_pendapatan_lembur_perperiode']
      ]);
    });

    return $result;
  }

  public function getPendapatanById(string $id): ?array
  {
    $pendapatan = $this->repository->findById($id);

    if (!$pendapatan) {
      return null;
    }

    $data = $pendapatan->toArray();

    $lemburDetail = $this->payrollService->calculateTotalPendapatan(
      $pendapatan->karyawan_id,
      $pendapatan->periode,
      $pendapatan->tunjangan
    );

    return array_merge($data, [
      'total_lembur_perperiode' => $lemburDetail['total_lembur_perperiode'],
      'total_pendapatan_lembur_perperiode' => $lemburDetail['total_pendapatan_lembur_perperiode']
    ]);
  }

  public function createPendapatan(array $data): array
  {
    // Cek apakah sudah ada pendapatan untuk karyawan di periode yang sama
    $existing = $this->repository->findByKaryawanAndPeriode($data['karyawan_id'], $data['periode']);
    if ($existing) {
      throw new \InvalidArgumentException('Pendapatan untuk karyawan pada periode ini sudah ada');
    }

    // Process calculation dengan rumus pendapatan
    $processedData = $this->payrollService->processPendapatanCalculation($data);

    $pendapatan = $this->repository->create($processedData);

    // Kembalikan dengan properties lembur
    $data = $pendapatan->toArray();
    $lemburDetail = $this->payrollService->calculateTotalPendapatan(
      $pendapatan->karyawan_id,
      $pendapatan->periode,
      $pendapatan->tunjangan
    );

    return array_merge($data, [
      'total_lembur_perperiode' => $lemburDetail['total_lembur_perperiode'],
      'total_pendapatan_lembur_perperiode' => $lemburDetail['total_pendapatan_lembur_perperiode']
    ]);
  }

  public function updatePendapatan(string $id, array $data): ?array
  {
    $existing = $this->repository->findById($id);
    if (!$existing) {
      return null;
    }

    // Jika ada perubahan yang mempengaruhi perhitungan
    $needsRecalculation = isset($data['karyawan_id']) ||
      isset($data['periode']) ||
      isset($data['tunjangan']);

    if ($needsRecalculation) {
      $karyawanId = $data['karyawan_id'] ?? $existing->karyawan_id;
      $periode = $data['periode'] ?? $existing->periode;
      $tunjangan = $data['tunjangan'] ?? $existing->tunjangan;

      // Cek unique constraint untuk update
      if (isset($data['karyawan_id']) || isset($data['periode'])) {
        $duplicate = $this->repository->findByKaryawanAndPeriode($karyawanId, $periode);
        if ($duplicate && $duplicate->id !== $id) {
          throw new \InvalidArgumentException('Pendapatan untuk karyawan pada periode ini sudah ada');
        }
      }

      $calculation = $this->payrollService->calculateTotalPendapatan($karyawanId, $periode, $tunjangan);
      $data = array_merge($data, [
        'total_pendapatan' => $calculation['total_pendapatan']
      ]);
    }

    $updated = $this->repository->update($id, $data);

    if (!$updated) {
      return null;
    }

    $updatedPendapatan = $this->repository->findById($id);
    $data = $updatedPendapatan->toArray();
    $lemburDetail = $this->payrollService->calculateTotalPendapatan(
      $updatedPendapatan->karyawan_id,
      $updatedPendapatan->periode,
      $updatedPendapatan->tunjangan
    );

    return array_merge($data, [
      'total_lembur_perperiode' => $lemburDetail['total_lembur_perperiode'],
      'total_pendapatan_lembur_perperiode' => $lemburDetail['total_pendapatan_lembur_perperiode']
    ]);
  }

  public function getPendapatanByKaryawan(string $karyawanId, array $filters = []): LengthAwarePaginator
  {
    $result = $this->repository->getByKaryawanId($karyawanId, $filters);
    $result->getCollection()->transform(function ($pendapatan) {
      $data = $pendapatan->toArray();

      $lemburDetail = $this->payrollService->calculateTotalPendapatan(
        $pendapatan->karyawan_id,
        $pendapatan->periode,
        $pendapatan->tunjangan
      );

      return array_merge($data, [
        'total_lembur_perperiode' => $lemburDetail['total_lembur_perperiode'],
        'total_pendapatan_lembur_perperiode' => $lemburDetail['total_pendapatan_lembur_perperiode']
      ]);
    });

    return $result;
  }

  public function deletePendapatan(string $id): bool
  {
    return $this->repository->delete($id);
  }

  public function recalculatePendapatan(string $periode): void
  {
    $this->payrollService->recalculatePendapatanByPeriode($periode);
  }
}
