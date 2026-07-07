<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\KomponenGaji;
use App\Models\Pangkalan;
use Illuminate\Support\Facades\DB;

class PayrollCalculationService
{
    // Menghitung total jam lembur berdasarkan aturan
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

    // Menghitung upah lembur per jam
    public function calculateUpahLemburPerJam(float $gajiPokok): float
    {
        // RUMUS: gaji_pokok / 173
        return $gajiPokok / 173;
    }

    // Menghitung rupiah lembur
    public function calculateRupiahLembur(float $gajiPokok, float $jamLembur): array
    {
        $totalJamLembur = $this->calculateTotalJamLembur($jamLembur);
        $upahPerJam = $this->calculateUpahLemburPerJam($gajiPokok);
        $rupiahLembur = round($upahPerJam * $totalJamLembur, 2);

        return [
            'total_jam_lembur' => round($totalJamLembur, 2),
            'upah_perjam' => round($upahPerJam, 2),
            'nominal' => $rupiahLembur,
        ];
    }

    // Memproses komponen gaji calculation (untuk lembur)
    public function processKomponenGajiCalculation(array $data): array
    {
        if ($data['tipe'] === 'LEMBUR') {
            $karyawan = Karyawan::findOrFail($data['karyawan_id']);
            $calculation = $this->calculateRupiahLembur($karyawan->gaji_pokok, $data['jam_lembur']);
            return array_merge($data, $calculation);
        }

        // Untuk TUNJANGAN, langsung pakai nominal dari input
        return $data;
    }

    // Menghitung total lembur per periode
    public function calculateTotalLemburPerPeriode(string $karyawanId, int $bulan, int $tahun): float
    {
        return KomponenGaji::where('karyawan_id', $karyawanId)
            ->where('tipe', 'LEMBUR')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->sum('nominal');
    }

    // Menghitung total tunjangan per periode
    public function calculateTotalTunjanganPerPeriode(string $karyawanId, int $bulan, int $tahun): float
    {
        return KomponenGaji::where('karyawan_id', $karyawanId)
            ->where('tipe', 'TUNJANGAN')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->sum('nominal');
    }

    // Menghitung potongan BPJS Kesehatan
    public function calculatePotonganBpjsKesehatan(float $gajiPokok, float $persenBpjsKesehatan): float
    {
        return round(($gajiPokok * $persenBpjsKesehatan) / 100, 2);
    }

    // Menghitung potongan BPJS Tenagakerja
    public function calculatePotonganBpjsTenagakerja(float $gajiPokok, float $persenBpjsTenagakerja): float
    {
        return round(($gajiPokok * $persenBpjsTenagakerja) / 100, 2);
    }

    // Menghitung total potongan
    public function calculateTotalPotongan(float $bpjsKesehatan, float $bpjsTenagakerja, float $potonganLainnya = 0): float
    {
        return round($bpjsKesehatan + $bpjsTenagakerja + $potonganLainnya, 2);
    }

    // Menghitung total pendapatan
    public function calculateTotalPendapatan(float $gajiPokok, float $totalLembur, float $totalTunjangan): float
    {
        return round($gajiPokok + $totalLembur + $totalTunjangan, 2);
    }

    // Menghitung gaji bersih
    public function calculateGajiBersih(float $totalPendapatan, float $totalPotongan, float $pph21 = 0): float
    {
        return round($totalPendapatan - $totalPotongan - $pph21, 2);
    }

    // Memproses perhitungan gaji karyawan berdasarkan komponen gaji 
    public function processGajiKaryawanCalculation(string $karyawanId, int $bulan, int $tahun, float $potonganLainnya = 0, float $pph21 = 0): array
    {
        // 1. Ambil data karyawan
        $karyawan = Karyawan::findOrFail($karyawanId);

        // 2. Hitung total lembur dari tb_komponen_gaji
        $totalLembur = $this->calculateTotalLemburPerPeriode($karyawanId, $bulan, $tahun);

        // 3. Hitung total tunjangan dari tb_komponen_gaji
        $totalTunjangan = $this->calculateTotalTunjanganPerPeriode($karyawanId, $bulan, $tahun);

        // 4. Hitung total pendapatan 
        $totalPendapatan = $this->calculateTotalPendapatan(
            $karyawan->gaji_pokok,
            $totalLembur,
            $totalTunjangan
        );

        // 5. Hitung potongan BPJS
        $potonganKesehatan = $this->calculatePotonganBpjsKesehatan(
            $karyawan->gaji_pokok,
            $karyawan->bpjs_kesehatan
        );

        $potonganTenagakerja = $this->calculatePotonganBpjsTenagakerja(
            $karyawan->gaji_pokok,
            $karyawan->bpjs_tenagakerja
        );

        // 6. Hitung total potongan
        $totalPotongan = $this->calculateTotalPotongan(
            $potonganKesehatan,
            $potonganTenagakerja,
            $potonganLainnya
        );

        // 7. Hitung gaji bersih
        $gajiBersih = $this->calculateGajiBersih(
            $totalPendapatan,
            $totalPotongan,
            $pph21
        );

        return [
            'karyawan_id' => $karyawanId,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'gapok' => round($karyawan->gaji_pokok, 2),
            'total_lembur' => $totalLembur,
            'total_tunjangan' => $totalTunjangan,
            'total_pendapatan' => $totalPendapatan,
            'potongan_bpjs_kesehatan' => $potonganKesehatan,
            'potongan_bpjs_tenagakerja' => $potonganTenagakerja,
            'potongan_lainnya' => round($potonganLainnya, 2),
            'total_potongan' => $totalPotongan,
            'pph21' => round($pph21, 2),
            'gaji_bersih' => $gajiBersih,
        ];
    }

    // Menghitung total transaksi operasional berdasarkan harga_satuan pangkalan
    public function calculateTransaksiOperasional(array $data): array
    {
        // Ambil harga_satuan dari pangkalan
        $pangkalan = Pangkalan::findOrFail($data['pangkalan_id']);

        $hargaSatuan = $pangkalan->harga_satuan;
        $total = $data['qty'] * $hargaSatuan;

        // Hitung debit/credit berdasarkan is_in (pemasukan atau pengeluaran)
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
            'credit' => $credit,
        ];
    }

    }
