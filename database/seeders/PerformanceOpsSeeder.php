<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\SumberKas;
use App\Models\Tabung;

class PerformanceOpsSeeder extends Seeder
{
    public function run()
    {
        ini_set('memory_limit', '1024M');
        DB::disableQueryLog();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tb_transaksi_operasional')->truncate();
        DB::table('tb_kas_perusahaan')->truncate();
        DB::table('tb_asset')->truncate();
        DB::table('tb_pangkalan')->truncate();
        DB::table('tb_tabung')->truncate();
        DB::table('tb_sumber_kas')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $totalRecords = 100000;
        $chunkSize = 2000;
        $batches = ceil($totalRecords / $chunkSize);

        $this->command->info("Membuat 10 SumberKas dan 10 Tabung...");
        
        $sumberKasIds = [];
        for ($i = 0; $i < 10; $i++) {
            $sumberKas = SumberKas::create([
                'tipe' => 'BANK',
                'nama_bank' => 'Bank ' . Str::random(5),
                'nomor_rekening' => (string) rand(1000000000, 9999999999),
                'atas_nama' => 'PT MIGAS ' . $i,
                'saldo_awal' => 100000000,
                'saldo_terakhir' => 100000000,
                'aktif' => true,
                'keterangan' => 'Kas ' . $i,
            ]);
            $sumberKasIds[] = $sumberKas->id;
        }

        $tabungIds = [];
        for ($i = 0; $i < 10; $i++) {
            $tabung = Tabung::create([
                'nama' => 'Tabung Gas ' . ['3KG', '5KG', '12KG'][rand(0, 2)],
                'berat' => rand(300, 1200) / 100,
            ]);
            $tabungIds[] = $tabung->id;
        }

        $this->command->info("Memulai pembuatan {$totalRecords} data Asset, Pangkalan, Transaksi, dan Kas Perusahaan...");

        for ($batch = 1; $batch <= $batches; $batch++) {
            $assetChunk = [];
            $pangkalanChunk = [];
            $transaksiChunk = [];
            $kasChunk = [];

            for ($i = 0; $i < $chunkSize; $i++) {
                // Generate Asset
                $assetId = Str::uuid()->toString();
                $assetChunk[] = [
                    'id' => $assetId,
                    'nama' => 'Kendaraan ' . Str::random(5),
                    'identitas' => 'B ' . rand(1000, 9999) . ' ' . chr(rand(65, 90)) . chr(rand(65, 90)),
                    'jumlah' => 1,
                    'catatan' => 'Baik',
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Generate Pangkalan
                $pangkalanId = Str::uuid()->toString();
                $pangkalanChunk[] = [
                    'id' => $pangkalanId,
                    'regist_id' => (int) ($batch . str_pad($i, 5, '0', STR_PAD_LEFT)),
                    'nama' => 'Pangkalan ' . Str::random(6),
                    'no_ktp' => (int) ('32' . str_pad($batch, 4, '0', STR_PAD_LEFT) . str_pad($i, 5, '0', STR_PAD_LEFT)),
                    'alamat' => 'Jalan ' . Str::random(10) . ' No ' . rand(1, 100),
                    'harga_satuan' => 15000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Generate Transaksi & Kas (Linked)
                $transaksiId = Str::uuid()->toString();
                $kasId = Str::uuid()->toString();
                
                $timestamp = rand(strtotime('2020-01-01'), strtotime('2026-06-30'));
                $tanggal = date('Y-m-d', $timestamp);
                $jenisTransaksiArray = ['PEMBELIAN_GAS', 'MAINTENANCE', 'PENJUALAN_GAS', 'LAINNYA'];
                $jenisTransaksi = $jenisTransaksiArray[array_rand($jenisTransaksiArray)];
                
                $isPemasukan = $jenisTransaksi === 'PENJUALAN_GAS' ? true : false;
                $prefix = $isPemasukan ? 'IN' : 'OUT';
                $noRef = "TRX-{$prefix}-" . date('Ymd', strtotime($tanggal)) . '-' . strtoupper(Str::random(6));

                $qty = rand(10, 100);
                $hargaSatuan = 15000;
                $jumlah = $qty * $hargaSatuan;
                $sumberKasId = $sumberKasIds[array_rand($sumberKasIds)];
                $tabungId = $tabungIds[array_rand($tabungIds)];

                $transaksiChunk[] = [
                    'id' => $transaksiId,
                    'tanggal' => $tanggal,
                    'no_ref' => $noRef,
                    'jenis_transaksi' => $jenisTransaksi,
                    'keterangan' => 'Test Transaksi ' . $jenisTransaksi,
                    'pangkalan_id' => $pangkalanId,
                    'tabung_id' => $tabungId,
                    'asset_id' => ($jenisTransaksi === 'MAINTENANCE') ? $assetId : null,
                    'is_pemasukan' => $isPemasukan,
                    'qty' => $qty,
                    'unit' => 'Tabung',
                    'harga_satuan' => $hargaSatuan,
                    'jumlah' => $jumlah,
                    'kas_perusahaan_id' => $kasId,
                    'created_by' => 'System',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $tipeKas = $isPemasukan ? 'DEBIT' : 'KREDIT';
                $kasChunk[] = [
                    'id' => $kasId,
                    'tanggal' => $tanggal,
                    'sumber_kas_id' => $sumberKasId,
                    'keterangan' => 'Kas untuk ' . $noRef,
                    'tipe_transaksi' => $tipeKas,
                    'jumlah' => $jumlah,
                    'transaksi_operasional_id' => $transaksiId,
                    'saldo_sebelum' => 100000000,
                    'saldo_sesudah' => 100000000 + ($isPemasukan ? $jumlah : -$jumlah),
                    'created_by' => 'System',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('tb_asset')->insert($assetChunk);
            DB::table('tb_pangkalan')->insert($pangkalanChunk);
            DB::table('tb_kas_perusahaan')->insert($kasChunk);
            DB::table('tb_transaksi_operasional')->insert($transaksiChunk);

            $this->command->info("Batch OPS {$batch} / {$batches} selesai.");
        }

        $this->command->info("Selesai membuat Data Operasional.");
    }
}
