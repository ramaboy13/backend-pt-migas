<?php

namespace App\Services;

use App\DTO\KomponenGaji\KomponenGajiCollectionDTO;
use App\DTO\KomponenGaji\KomponenGajiDTO;
use App\Repositories\KomponenGajiRepository;
use Illuminate\Support\Facades\DB;

class KomponenGajiService
{
    public function __construct(
        private KomponenGajiRepository $repository,
        private PayrollCalculationService $payrollService
    ) {}

    public function getAllKomponen(array $filters = [], int $perPage = 10, bool $withKaryawan = true): KomponenGajiCollectionDTO
    {
        $komponen = $this->repository->getAll($filters, $perPage, $withKaryawan);

        return KomponenGajiCollectionDTO::fromPaginator($komponen, $withKaryawan);
    }

    public function getKomponenById(string $id, bool $withKaryawan = true): ?KomponenGajiDTO
    {
        $komponen = $this->repository->findById($id, $withKaryawan);

        if (! $komponen) {
            return null;
        }

        return KomponenGajiDTO::fromModel($komponen, $withKaryawan);
    }

    public function createKomponen(array $data): KomponenGajiDTO
    {
        return DB::transaction(function () use ($data) {
            // Process calculation (untuk lembur)
            $processedData = $this->payrollService->processKomponenGajiCalculation($data);

            // Create komponen gaji
            $komponen = $this->repository->create($processedData);

            return KomponenGajiDTO::fromModel($komponen, true);
        });
    }

    public function updateKomponen(string $id, array $data): ?KomponenGajiDTO
    {
        return DB::transaction(function () use ($id, $data) {
            $existing = $this->repository->findById($id, false);

            if (! $existing) {
                return null;
            }

            // Jika jam_lembur diupdate, recalculate untuk tipe LEMBUR
            if ($existing->tipe === 'LEMBUR') {
                if (isset($data['jam_lembur']) || isset($data['karyawan_id'])) {
                    $karyawanId = $data['karyawan_id'] ?? $existing->karyawan_id;
                    $jamLembur = $data['jam_lembur'] ?? $existing->jam_lembur;

                    $karyawan = \App\Models\Karyawan::findOrFail($karyawanId);
                    // PERBAIKAN: Gunakan gaji_pokok
                    $calculation = $this->payrollService->calculateRupiahLembur($karyawan->gaji_pokok, $jamLembur);

                    $data = array_merge($data, $calculation);
                }
            }

            $updated = $this->repository->update($id, $data);

            if (! $updated) {
                return null;
            }

            return $this->getKomponenById($id);
        });
    }

    public function deleteKomponen(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->delete($id);
        });
    }

    public function getKomponenByKaryawan(string $karyawanId, array $filters = []): KomponenGajiCollectionDTO
    {
        $komponen = $this->repository->getByKaryawanId($karyawanId, $filters);

        return KomponenGajiCollectionDTO::fromPaginator($komponen, true);
    }

    }
