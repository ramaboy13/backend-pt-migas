<?php

namespace App\Services;

use App\DTO\LemburKaryawan\LemburKaryawanCollectionDTO;
use App\DTO\LemburKaryawan\LemburKaryawanDTO;
use App\Repositories\LemburKaryawanRepository;

class LemburKaryawanService
{
    public function __construct(
        private LemburKaryawanRepository $repository,
        private PayrollCalculationService $payrollService
    ) {}

    public function getAllLembur(array $filters = [], bool $withKaryawan = true): LemburKaryawanCollectionDTO
    {
        $lemburs = $this->repository->getAll($filters);

        return LemburKaryawanCollectionDTO::fromPaginator($lemburs, $withKaryawan);
    }

    public function getLemburById(string $id): ?LemburKaryawanDTO
    {
        $lembur = $this->repository->findById($id);

        if (! $lembur) {
            return null;
        }

        return LemburKaryawanDTO::fromModel($lembur);
    }

    public function createLembur(array $data): LemburKaryawanDTO
    {
        // Process calculation dengan rumus lembur
        $processedData = $this->payrollService->processLemburCalculation($data);
        $lembur = $this->repository->create($processedData);

        return LemburKaryawanDTO::fromModel($lembur);
    }

    public function updateLembur(string $id, array $data): ?LemburKaryawanDTO
    {
        // Jika jam_lembur diupdate, recalculate
        if (isset($data['jam_lembur']) || isset($data['karyawan_id'])) {
            $existing = $this->repository->findById($id);
            if (! $existing) {
                return null;
            }

            $jamLembur = $data['jam_lembur'] ?? $existing->jam_lembur;
            $karyawanId = $data['karyawan_id'] ?? $existing->karyawan_id;

            $calculation = $this->payrollService->calculateRupiahLembur($karyawanId, $jamLembur);
            $data = array_merge($data, $calculation);
        }

        $updated = $this->repository->update($id, $data);

        if (! $updated) {
            return null;
        }

        $lembur = $this->repository->findById($id);

        return LemburKaryawanDTO::fromModel($lembur);
    }

    public function deleteLembur(string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getLemburByKaryawan(string $karyawanId, array $filters = [], bool $withKaryawan = false): LemburKaryawanCollectionDTO
    {
        $lemburs = $this->repository->getByKaryawanId($karyawanId, $filters);

        return LemburKaryawanCollectionDTO::fromPaginator($lemburs, $withKaryawan);
    }
}
