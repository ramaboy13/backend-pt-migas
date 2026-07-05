<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PerformanceHrSeeder extends Seeder
{
    public function run()
    {
        ini_set('memory_limit', '1024M');
        DB::disableQueryLog();

        $totalRecords = 100000;
        $chunkSize = 2000;
        $batches = ceil($totalRecords / $chunkSize);

        $jabatans = ['Staff', 'Manager', 'Supervisor', 'Operator', 'Teknisi', 'Admin', 'Direktur'];

        $this->command->info("Memulai pembuatan {$totalRecords} data Karyawan, Komponen Gaji, dan Gaji Karyawan...");

        for ($batch = 1; $batch <= $batches; $batch++) {
            $karyawanChunk = [];
            $komponenGajiChunk = [];
            $gajiKaryawanChunk = [];

            for ($i = 0; $i < $chunkSize; $i++) {
                $karyawanId = Str::uuid()->toString();
                
                // Random date between 2020 and 2026 for Karyawan
                $tglMasuk = $faker->dateTimeBetween('2020-01-01', '2026-06-30')->format('Y-m-d');
                $gapok = $faker->randomElement([4000000, 5000000, 6000000, 7000000, 8000000]);

                $karyawanChunk[] = [
                    'id' => $karyawanId,
                    'nik' => (string) rand(1000000000000000, 9999999999999999),
                    'nama' => 'Karyawan ' . Str::random(8),
                    'jabatan' => $jabatans[array_rand($jabatans)],
                    'gaji_pokok' => $gapok,
                    'bpjs_kesehatan' => 1, // 1%
                    'bpjs_tenagakerja' => 2, // 2%
                    'tgl_masuk' => $tglMasuk,
                    'aktif' => true,
                    'alamat' => 'Jalan ' . Str::random(10) . ' No. ' . rand(1, 999),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Generate 1 Komponen Gaji per karyawan for the same month/year
                $komponenGajiId = Str::uuid()->toString();
                // Random date between 2020 and 2026
                $timestamp = rand(strtotime('2020-01-01'), strtotime('2026-06-30'));
                $tanggalKomponen = date('Y-m-d', $timestamp);
                $tipe = rand(0, 1) === 0 ? 'LEMBUR' : 'TUNJANGAN';
                $jamLembur = $tipe === 'LEMBUR' ? rand(1, 10) : 0;
                $upahPerjam = $tipe === 'LEMBUR' ? 25000 : 0;
                $nominalKomponen = $tipe === 'LEMBUR' ? ($jamLembur * $upahPerjam) : [100000, 200000, 300000][rand(0,2)];

                $komponenGajiChunk[] = [
                    'id' => $komponenGajiId,
                    'tipe' => $tipe,
                    'karyawan_id' => $karyawanId,
                    'tanggal' => $tanggalKomponen,
                    'jam_lembur' => $jamLembur,
                    'total_jam_lembur' => $jamLembur,
                    'upah_perjam' => $upahPerjam,
                    'nominal' => $nominalKomponen,
                    'keterangan' => 'Performance Test ' . $tipe,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Generate 1 Gaji Karyawan per karyawan
                $gajiKaryawanId = Str::uuid()->toString();
                $totalLembur = $tipe === 'LEMBUR' ? $nominalKomponen : 0;
                $totalTunjangan = $tipe === 'TUNJANGAN' ? $nominalKomponen : 0;
                $totalPendapatan = $gapok + $totalLembur + $totalTunjangan;
                
                $potBpjsKes = $gapok * 0.01;
                $potBpjsTk = $gapok * 0.02;
                $potLainnya = 0;
                $totalPotongan = $potBpjsKes + $potBpjsTk + $potLainnya;
                $pph21 = 0;
                $gajiBersih = $totalPendapatan - $totalPotongan - $pph21;

                $gajiKaryawanChunk[] = [
                    'id' => $gajiKaryawanId,
                    'karyawan_id' => $karyawanId,
                    'bulan' => (int) $tanggalKomponen->format('m'),
                    'tahun' => (int) $tanggalKomponen->format('Y'),
                    'tanggal_gaji' => $tanggalKomponen->format('Y-m-25'),
                    'gapok' => $gapok,
                    'total_lembur' => $totalLembur,
                    'total_tunjangan' => $totalTunjangan,
                    'total_pendapatan' => $totalPendapatan,
                    'potongan_bpjs_kesehatan' => $potBpjsKes,
                    'potongan_bpjs_tenagakerja' => $potBpjsTk,
                    'potongan_lainnya' => $potLainnya,
                    'total_potongan' => $totalPotongan,
                    'pph21' => $pph21,
                    'gaji_bersih' => $gajiBersih,
                    'status' => 'Telah Dibayar',
                    'processed_by' => 'System',
                    'processed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('tb_karyawan')->insert($karyawanChunk);
            DB::table('tb_komponen_gaji')->insert($komponenGajiChunk);
            DB::table('tb_gaji_karyawan')->insert($gajiKaryawanChunk);

            $this->command->info("Batch HR {$batch} / {$batches} selesai.");
        }

        $this->command->info("Selesai membuat HR Data.");
    }
}
