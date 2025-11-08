<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\LemburKaryawan;
use App\Models\Pendapatan;
use App\Models\Potongan;
use Illuminate\Support\Facades\DB;

class PayrollCalculationService
{
  /**
   * Calculate total jam lembur berdasarkan rules
   */
  public function calculateTotalJamLembur(float $jamLembur): float
  {
    // RUMUS: 
    // 1-2 jam → 2 jam
    // 2-4 jam → 7.5 jam  
    // >5 jam → 9.5 jam

    if ($jamLembur >= 1 && $jamLembur < 2) {
      return 2.0;
    } elseif ($jamLembur >= 2 && $jamLembur <= 4) {
      return 7.5;
    } elseif ($jamLembur > 5) {
      return 9.5;
    }

    // Untuk jam lembur di bawah 1 jam, return asli
    return $jamLembur;
  }

  /**
   * Calculate upah lembur per jam
   */
  public function calculateUpahLemburPerJam(string $karyawanId): float
  {
    $karyawan = Karyawan::findOrFail($karyawanId);
    // RUMUS: gapok / 173
    return $karyawan->gapok / 173;
  }

  /**
   * Calculate rupiah lembur
   */
  public function calculateRupiahLembur(string $karyawanId, float $jamLembur): array
  {
    $totalJamLembur = $this->calculateTotalJamLembur($jamLembur);
    $upahPerJam = $this->calculateUpahLemburPerJam($karyawanId);
    $rupiahLembur = round($upahPerJam * $totalJamLembur, 2);
    $upahPerJam = round($upahPerJam, 2);

    return [
      'total_jam_lembur' => round($totalJamLembur, 2),
      'upah_lembur_perjam' => $upahPerJam,
      'rupiah_lembur' => $rupiahLembur
    ];
  }

  /**
   * Process lembur calculation and create/update record
   */
  public function processLemburCalculation(array $data): array
  {
    return DB::transaction(function () use ($data) {
      $calculation = $this->calculateRupiahLembur(
        $data['karyawan_id'],
        $data['jam_lembur']
      );

      return array_merge($data, $calculation);
    });
  }

  /**
   * Calculate total pendapatan untuk suatu periode
   */
  public function calculateTotalPendapatan(string $karyawanId, string $periode, float $tunjangan = 0): array
  {
    $karyawan = Karyawan::findOrFail($karyawanId);
    // Hitung total lembur untuk periode tersebut (bulan dan tahun yang sama)
    $lemburRecords = LemburKaryawan::where('karyawan_id', $karyawanId)
      ->whereYear('tanggal', date('Y', strtotime($periode)))
      ->whereMonth('tanggal', date('m', strtotime($periode)))
      ->get();
    $totalLembur = $lemburRecords->sum('rupiah_lembur');
    $totalPendapatan = $karyawan->gapok + $tunjangan + $totalLembur;

    return [
      'gapok' => $karyawan->gapok,
      'tunjangan' => $tunjangan,
      'total_lembur' => $totalLembur,
      'total_pendapatan' => $totalPendapatan,
      'total_lembur_perperiode' => $lemburRecords->count(),
      'total_pendapatan_lembur_perperiode' => $totalLembur,
    ];
  }

  /**
   * Process pendapatan calculation and create/update record
   */
  public function processPendapatanCalculation(array $data): array
  {
    return DB::transaction(function () use ($data) {
      $calculation = $this->calculateTotalPendapatan(
        $data['karyawan_id'],
        $data['periode'],
        $data['tunjangan'] ?? 0
      );

      return array_merge($data, ['total_pendapatan' => $calculation['total_pendapatan']]);
    });
  }

  /**
   * Recalculate all pendapatan for a specific periode (useful when lembur data changes)
   */
  public function recalculatePendapatanByPeriode(string $periode): void
  {
    $pendapatans = Pendapatan::where('periode', $periode)->get();

    foreach ($pendapatans as $pendapatan) {
      $calculation = $this->calculateTotalPendapatan(
        $pendapatan->karyawan_id,
        $pendapatan->periode,
        $pendapatan->tunjangan
      );

      $pendapatan->update([
        'total_pendapatan' => $calculation['total_pendapatan']
      ]);
    }
  }

  /**
   * Calculate potongan BPJS untuk suatu periode
   */
  public function calculatePotongan(string $karyawanId): array
  {
    $karyawan = Karyawan::findOrFail($karyawanId);

    $rp_bpjs_kesehatan = ($karyawan->gapok * $karyawan->bpjs_kesehatan) / 100;
    $rp_bpjs_tenagakerja = ($karyawan->gapok * $karyawan->bpjs_tenagakerja) / 100;
    $total_potongan = $rp_bpjs_kesehatan + $rp_bpjs_tenagakerja;

    return [
      'rp_bpjs_kesehatan' => round($rp_bpjs_kesehatan, 2),
      'rp_bpjs_tenagakerja' => round($rp_bpjs_tenagakerja, 2),
      'total_potongan' => round($total_potongan, 2),
      'gapok' => $karyawan->gapok,
      'persen_bpjs_kesehatan' => $karyawan->bpjs_kesehatan,
      'persen_bpjs_tenagakerja' => $karyawan->bpjs_tenagakerja
    ];
  }

  /**
   * Process potongan calculation and create/update record
   */
  public function processPotonganCalculation(array $data): array
  {
    return DB::transaction(function () use ($data) {
      $calculation = $this->calculatePotongan($data['karyawan_id']);

      return array_merge($data, $calculation);
    });
  }

  /**
   * Recalculate all potongan for a specific periode (useful when karyawan data changes)
   */
  public function recalculatePotonganByPeriode(string $periode): void
  {
    $potongans = Potongan::where('periode', $periode)->get();

    foreach ($potongans as $potongan) {
      $calculation = $this->calculatePotongan($potongan->karyawan_id);

      $potongan->update([
        'rp_bpjs_kesehatan' => $calculation['rp_bpjs_kesehatan'],
        'rp_bpjs_tenagakerja' => $calculation['rp_bpjs_tenagakerja'],
        'total_potongan' => $calculation['total_potongan']
      ]);
    }
  }

  /**
   * Recalculate potongan when karyawan data changes (gapok or BPJS percentages)
   */
  public function recalculatePotonganByKaryawan(string $karyawanId): void
  {
    $potongans = Potongan::where('karyawan_id', $karyawanId)->get();

    foreach ($potongans as $potongan) {
      $calculation = $this->calculatePotongan($karyawanId);

      $potongan->update([
        'rp_bpjs_kesehatan' => $calculation['rp_bpjs_kesehatan'],
        'rp_bpjs_tenagakerja' => $calculation['rp_bpjs_tenagakerja'],
        'total_potongan' => $calculation['total_potongan']
      ]);
    }
  }
}
