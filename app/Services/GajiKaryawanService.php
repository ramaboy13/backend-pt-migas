<?php

namespace App\Services;

use App\DTO\GajiKaryawan\GajiKaryawanCollectionDTO;
use App\DTO\GajiKaryawan\GajiKaryawanDTO;
use App\Repositories\GajiKaryawanRepository;
use Illuminate\Support\Facades\DB;

class GajiKaryawanService
{
    public function __construct(
        private GajiKaryawanRepository $repository,
        private PayrollCalculationService $payrollService
    ) {}

    public function getAllGaji(array $filters = [], int $perPage = 10, bool $withRelations = true): GajiKaryawanCollectionDTO
    {
        $gajiList = $this->repository->getAll($filters, $perPage, $withRelations);

        return GajiKaryawanCollectionDTO::fromPaginator($gajiList, $withRelations);
    }

    public function getGajiById(string $id, bool $withRelations = true): ?GajiKaryawanDTO
    {
        $gaji = $this->repository->findById($id, $withRelations);

        if (! $gaji) {
            return null;
        }

        return GajiKaryawanDTO::fromModel($gaji, $withRelations);
    }

    public function createGaji(array $data): GajiKaryawanDTO
    {
        return DB::transaction(function () use ($data) {
            // Cek apakah sudah ada gaji untuk karyawan di periode yang sama
            $existing = $this->repository->findByKaryawanAndPeriode(
                $data['karyawan_id'],
                $data['bulan'],
                $data['tahun']
            );

            if ($existing) {
                throw new \InvalidArgumentException('Gaji untuk karyawan pada periode ini sudah ada');
            }

            // Process calculation dengan PayrollCalculationService
            $calculation = $this->payrollService->processGajiKaryawanCalculation(
                $data['karyawan_id'],
                $data['bulan'],
                $data['tahun'],
                $data['potongan_lainnya'] ?? 0,
                $data['pph21'] ?? 0
            );

            // Menggabungkan data dari input dan hasil perhitungan
            $processedData = array_merge($data, $calculation);
            $processedData['tanggal_gaji'] = $data['tanggal_gaji'] ?? now();

            if (isset($processedData['status']) && $processedData['status'] === 'Telah Dibayar') {
                $processedData['processed_by'] = \Illuminate\Support\Facades\Auth::user()?->name ?? 'System';
                $processedData['processed_at'] = now();
            }

            // Create gaji karyawan
            $gaji = $this->repository->create($processedData);

            return GajiKaryawanDTO::fromModel($gaji, true);
        });
    }

    public function updateGaji(string $id, array $data): ?GajiKaryawanDTO
    {
        return DB::transaction(function () use ($id, $data) {
            $existing = $this->repository->findById($id, false);

            if (! $existing) {
                return null;
            }

            // Cek unique constraint jika ada perubahan karyawan atau periode
            if ((isset($data['karyawan_id']) && $data['karyawan_id'] !== $existing->karyawan_id) ||
                (isset($data['bulan']) && $data['bulan'] !== $existing->bulan) ||
                (isset($data['tahun']) && $data['tahun'] !== $existing->tahun)) {

                $karyawanId = $data['karyawan_id'] ?? $existing->karyawan_id;
                $bulan = $data['bulan'] ?? $existing->bulan;
                $tahun = $data['tahun'] ?? $existing->tahun;

                $duplicate = $this->repository->findByKaryawanAndPeriode($karyawanId, $bulan, $tahun);
                if ($duplicate && $duplicate->id !== $id) {
                    throw new \InvalidArgumentException('Gaji untuk karyawan pada periode ini sudah ada');
                }
            }

            // Jika ada perubahan yang mempengaruhi perhitungan
            $needsRecalculation = isset($data['potongan_lainnya']) ||
                                  isset($data['pph21']) ||
                                  isset($data['karyawan_id']) ||
                                  isset($data['bulan']) ||
                                  isset($data['tahun']);

            if ($needsRecalculation) {
                $karyawanId = $data['karyawan_id'] ?? $existing->karyawan_id;
                $bulan = $data['bulan'] ?? $existing->bulan;
                $tahun = $data['tahun'] ?? $existing->tahun;
                $potonganLainnya = $data['potongan_lainnya'] ?? $existing->potongan_lainnya;
                $pph21 = $data['pph21'] ?? $existing->pph21;

                $calculation = $this->payrollService->processGajiKaryawanCalculation(
                    $karyawanId,
                    $bulan,
                    $tahun,
                    $potonganLainnya,
                    $pph21
                );

                $data = array_merge($data, $calculation);
            }

            if (isset($data['status']) && $data['status'] === 'Telah Dibayar') {
                if (!isset($data['processed_by'])) {
                    $data['processed_by'] = \Illuminate\Support\Facades\Auth::user()?->name ?? 'System';
                }
                if (!isset($data['processed_at'])) {
                    $data['processed_at'] = now();
                }
            } elseif (isset($data['status']) && $data['status'] === 'Belum Dibayar') {
                $data['processed_by'] = null;
                $data['processed_at'] = null;
            }

            $updated = $this->repository->update($id, $data);

            if (! $updated) {
                return null;
            }

            return $this->getGajiById($id);
        });
    }

    public function deleteGaji(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->delete($id);
        });
    }

    public function getGajiByKaryawan(string $karyawanId, array $filters = []): GajiKaryawanCollectionDTO
    {
        $gajiList = $this->repository->getByKaryawanId($karyawanId, $filters);

        return GajiKaryawanCollectionDTO::fromPaginator($gajiList, true);
    }

    public function getSummaryByPeriode(int $bulan, int $tahun): array
    {
        return $this->repository->getSummaryByPeriode($bulan, $tahun);
    }

    /**
     * Generate gaji untuk semua karyawan aktif di periode tertentu
     */
    public function generateGajiForAllKaryawan(int $bulan, int $tahun, float $potonganLainnya = 0, float $pph21 = 0): array
    {
        $totalKaryawan = \App\Models\Karyawan::where('aktif', true)->count();
        
        $results = [];
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        \App\Models\Karyawan::where('aktif', true)->chunk(100, function ($karyawanList) use ($bulan, $tahun, $potonganLainnya, $pph21, &$results, &$successCount, &$failedCount, &$errors) {
            DB::transaction(function () use ($karyawanList, $bulan, $tahun, $potonganLainnya, $pph21, &$results, &$successCount, &$failedCount, &$errors) {
                foreach ($karyawanList as $karyawan) {
                    try {
                        $existing = $this->repository->findByKaryawanAndPeriode($karyawan->id, $bulan, $tahun);

                        if ($existing) {
                            $calculation = $this->payrollService->processGajiKaryawanCalculation(
                                $karyawan->id,
                                $bulan,
                                $tahun,
                                $potonganLainnya,
                                $pph21
                            );
                            $existing->update($calculation);
                            $results[] = ['karyawan' => $karyawan->nama, 'status' => 'updated'];
                            $successCount++;
                        } else {
                            $calculation = $this->payrollService->processGajiKaryawanCalculation(
                                $karyawan->id,
                                $bulan,
                                $tahun,
                                $potonganLainnya,
                                $pph21
                            );
                            $calculation['tanggal_gaji'] = now();
                            $calculation['status'] = 'PROCESSED';
                            $calculation['processed_at'] = now();

                            $this->repository->create($calculation);
                            $results[] = ['karyawan' => $karyawan->nama, 'status' => 'created'];
                            $successCount++;
                        }
                    } catch (\Exception $e) {
                        $failedCount++;
                        $errors[] = ['karyawan' => $karyawan->nama, 'error' => $e->getMessage()];
                    }
                }
            });
        });

        return [
            'success' => true,
            'total_karyawan' => $totalKaryawan,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'results' => $results,
            'errors' => $errors,
        ];
    }

    /**
     * Update status gaji
     */
    public function updateStatus(string $id, string $status): ?GajiKaryawanDTO
    {
        $updateData = [
            'status' => $status,
            'processed_at' => now(),
        ];

        if ($status === 'Telah Dibayar') {
            $updateData['processed_by'] = \Illuminate\Support\Facades\Auth::user()?->name ?? 'System';
        }

        $updated = $this->repository->update($id, $updateData);

        if (! $updated) {
            return null;
        }

        return $this->getGajiById($id);
    }
}
