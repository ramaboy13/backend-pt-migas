<?php

// app/Services/PendapatanService.php

namespace App\Services;

use App\DTO\PendapatanKaryawan\PendapatanKaryawanCollectionDTO;
use App\DTO\PendapatanKaryawan\PendapatanKaryawanDTO;
use App\Repositories\PendapatanRepository;

class PendapatanService
{
    public function __construct(
        private PendapatanRepository $repository,
        private PayrollCalculationService $payrollService
    ) {}

    public function getAllPendapatan(array $filters = []): PendapatanKaryawanCollectionDTO
    {
        $result = $this->repository->getAll($filters);

        // Collect lembur details for all items
        $lemburDetails = [];
        foreach ($result->items() as $pendapatan) {
            $lemburDetails[] = $this->payrollService->calculateTotalPendapatan(
                $pendapatan->karyawan_id,
                $pendapatan->periode,
                $pendapatan->tunjangan
            );
        }

        return PendapatanKaryawanCollectionDTO::fromPaginator($result, $lemburDetails);
    }

    public function getPendapatanById(string $id): ?PendapatanKaryawanDTO
    {
        $pendapatan = $this->repository->findById($id);

        if (! $pendapatan) {
            return null;
        }

        $lemburDetail = $this->payrollService->calculateTotalPendapatan(
            $pendapatan->karyawan_id,
            $pendapatan->periode,
            $pendapatan->tunjangan
        );

        return PendapatanKaryawanDTO::fromModel($pendapatan, $lemburDetail);
    }

    public function createPendapatan(array $data): PendapatanKaryawanDTO
    {
        // Cek apakah sudah ada pendapatan untuk karyawan di periode yang sama
        $existing = $this->repository->findByKaryawanAndPeriode($data['karyawan_id'], $data['periode']);
        if ($existing) {
            throw new \InvalidArgumentException('Pendapatan untuk karyawan pada periode ini sudah ada');
        }

        // Process calculation dengan rumus pendapatan
        $processedData = $this->payrollService->processPendapatanCalculation($data);
        $pendapatan = $this->repository->create($processedData);

        // Get lembur detail for the created record
        $lemburDetail = $this->payrollService->calculateTotalPendapatan(
            $pendapatan->karyawan_id,
            $pendapatan->periode,
            $pendapatan->tunjangan
        );

        return PendapatanKaryawanDTO::fromModel($pendapatan, $lemburDetail);
    }

    public function updatePendapatan(string $id, array $data): ?PendapatanKaryawanDTO
    {
        $existing = $this->repository->findById($id);
        if (! $existing) {
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
                'total_pendapatan' => $calculation['total_pendapatan'],
            ]);
        }

        $updated = $this->repository->update($id, $data);

        if (! $updated) {
            return null;
        }

        $updatedPendapatan = $this->repository->findById($id);
        $lemburDetail = $this->payrollService->calculateTotalPendapatan(
            $updatedPendapatan->karyawan_id,
            $updatedPendapatan->periode,
            $updatedPendapatan->tunjangan
        );

        return PendapatanKaryawanDTO::fromModel($updatedPendapatan, $lemburDetail);
    }

    public function getPendapatanByKaryawan(string $karyawanId, array $filters = []): PendapatanKaryawanCollectionDTO
    {
        $result = $this->repository->getByKaryawanId($karyawanId, $filters);

        // Collect lembur details for all items
        $lemburDetails = [];
        foreach ($result->items() as $pendapatan) {
            $lemburDetails[] = $this->payrollService->calculateTotalPendapatan(
                $pendapatan->karyawan_id,
                $pendapatan->periode,
                $pendapatan->tunjangan
            );
        }

        return PendapatanKaryawanCollectionDTO::fromPaginator($result, $lemburDetails);
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
