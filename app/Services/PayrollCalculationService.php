<?php

namespace App\Services;

use App\Models\GajiKaryawan;
use App\Models\Karyawan;
use App\Models\LemburKaryawan;
use App\Models\Pendapatan;
use App\Models\Potongan;
use App\Models\Pangkalan;
use App\Models\Tabung;
use App\Models\TransaksiOperasional;
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

  /**
   * Calculate gaji karyawan (MAIN calculation)
   */
  public function calculateGajiKaryawan(string $pendapatanId, string $potonganId, float $pph21 = 0): array
  {
    $pendapatan = Pendapatan::findOrFail($pendapatanId);
    $potongan = Potongan::findOrFail($potonganId);

    // Validasi: pastikan pendapatan dan potongan untuk karyawan yang sama
    if ($pendapatan->karyawan_id !== $potongan->karyawan_id) {
      throw new \InvalidArgumentException('Pendapatan dan Potongan harus untuk karyawan yang sama');
    }

    // Validasi: pastikan pendapatan dan potongan untuk periode yang sama
    // Gunakan date() untuk membandingkan hanya tanggalnya saja (ignore time)
    $pendapatanPeriode = date('Y-m-d', strtotime($pendapatan->periode));
    $potonganPeriode = date('Y-m-d', strtotime($potongan->periode));

    if ($pendapatanPeriode !== $potonganPeriode) {
      throw new \InvalidArgumentException('Pendapatan dan Potongan harus untuk periode yang sama. Pendapatan: ' . $pendapatanPeriode . ', Potongan: ' . $potonganPeriode);
    }

    $subtotal = $pendapatan->total_pendapatan - $potongan->total_potongan;
    $gajiBersih = $subtotal - $pph21;

    return [
      'subtotal' => round($subtotal, 2),
      'gaji_bersih' => round($gajiBersih, 2),
      'total_pendapatan' => $pendapatan->total_pendapatan,
      'total_potongan' => $potongan->total_potongan,
      'pph21' => round($pph21, 2),
      'karyawan_id' => $pendapatan->karyawan_id,
      'periode' => $pendapatanPeriode // Gunakan format yang konsisten
    ];
  }

  /**
   * Process gaji karyawan calculation and create/update record
   */
  public function processGajiKaryawanCalculation(array $data): array
  {
    return DB::transaction(function () use ($data) {
      $calculation = $this->calculateGajiKaryawan(
        $data['pendapatan_id'],
        $data['potongan_id'],
        $data['pph21'] ?? 0
      );

      return array_merge($data, $calculation);
    });
  }

  /**
   * Recalculate all gaji for a specific periode (useful when pendapatan/potongan changes)
   */
  public function recalculateGajiByPeriode(string $periode): void
  {
    $gajiKaryawans = GajiKaryawan::where('periode', $periode)->get();

    foreach ($gajiKaryawans as $gaji) {
      $calculation = $this->calculateGajiKaryawan(
        $gaji->pendapatan_id,
        $gaji->potongan_id,
        $gaji->pph21
      );

      $gaji->update([
        'subtotal' => $calculation['subtotal'],
        'gaji_bersih' => $calculation['gaji_bersih']
      ]);
    }
  }

  /**
   * Recalculate gaji when pendapatan changes
   */
  public function recalculateGajiByPendapatan(string $pendapatanId): void
  {
    $gajiKaryawans = GajiKaryawan::where('pendapatan_id', $pendapatanId)->get();

    foreach ($gajiKaryawans as $gaji) {
      $calculation = $this->calculateGajiKaryawan(
        $gaji->pendapatan_id,
        $gaji->potongan_id,
        $gaji->pph21
      );

      $gaji->update([
        'subtotal' => $calculation['subtotal'],
        'gaji_bersih' => $calculation['gaji_bersih']
      ]);
    }
  }

  /**
   * Recalculate gaji when potongan changes
   */
  public function recalculateGajiByPotongan(string $potonganId): void
  {
    $gajiKaryawans = GajiKaryawan::where('potongan_id', $potonganId)->get();

    foreach ($gajiKaryawans as $gaji) {
      $calculation = $this->calculateGajiKaryawan(
        $gaji->pendapatan_id,
        $gaji->potongan_id,
        $gaji->pph21
      );

      $gaji->update([
        'subtotal' => $calculation['subtotal'],
        'gaji_bersih' => $calculation['gaji_bersih']
      ]);
    }
  }

  /**
   * Generate slip gaji data for PDF/export
   */
  public function generateSlipGaji(string $gajiKaryawanId): array
  {
    $gaji = GajiKaryawan::with(['karyawan', 'pendapatan', 'potongan'])->findOrFail($gajiKaryawanId);

    return [
      'slip_data' => [
        'periode' => $gaji->periode,
        'karyawan' => [
          'nik' => $gaji->karyawan->NIK,
          'nama' => $gaji->karyawan->nama,
          'jabatan' => $gaji->karyawan->jabatan
        ],
        'pendapatan' => [
          'gapok' => $gaji->karyawan->gapok,
          'tunjangan' => $gaji->pendapatan->tunjangan,
          'total_lembur' => $gaji->pendapatan->total_pendapatan - $gaji->karyawan->gapok - $gaji->pendapatan->tunjangan,
          'total_pendapatan' => $gaji->pendapatan->total_pendapatan
        ],
        'potongan' => [
          'bpjs_kesehatan' => $gaji->potongan->rp_bpjs_kesehatan,
          'bpjs_tenagakerja' => $gaji->potongan->rp_bpjs_tenagakerja,
          'total_potongan' => $gaji->potongan->total_potongan
        ],
        'rincian_gaji' => [
          'subtotal' => $gaji->subtotal,
          'pph21' => $gaji->pph21,
          'gaji_bersih' => $gaji->gaji_bersih
        ]
      ]
    ];
  }

     /**
     * Calculate transaksi operasional total based on pangkalan harga_satuan
     */
    public function calculateTransaksiOperasional(array $data): array
    {
        // Ambil harga_satuan dari pangkalan
        $pangkalan = Pangkalan::findOrFail($data['pangkalan_id']);
        
        $hargaSatuan = $pangkalan->harga_satuan;
        $total = $data['qty'] * $hargaSatuan;
        
        // Hitung debit/credit berdasarkan is_in
        if ($data['is_in']) {
            $debit = $total;
            $credit = 0;
        } else {
            $debit = 0;
            $credit = $total;
        }

        return [
            'total' => $total,
            'debit' => $debit,
            'credit' => $credit
        ];
    }

    /**
     * Process transaksi operasional calculation
     */
    public function processTransaksiCalculation(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $calculation = $this->calculateTransaksiOperasional($data);
            return array_merge($data, $calculation);
        });
    }

}
