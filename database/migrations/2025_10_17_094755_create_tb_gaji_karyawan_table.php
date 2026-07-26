<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tb_gaji_karyawan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('karyawan_id');
            $table->integer('bulan');
            $table->integer('tahun');
            $table->date('tanggal_gaji');

            // Komponen Pendapatan (diambil dari karyawan + komponen_gaji)
            $table->decimal('gapok', 15, 2); // dari tb_karyawan
            $table->decimal('total_lembur', 15, 2)->default(0); // SUM dari tb_komponen_gaji (tipe LEMBUR)
            $table->decimal('total_tunjangan', 15, 2)->default(0); // SUM dari tb_komponen_gaji (tipe TUNJANGAN)
            $table->decimal('total_pendapatan', 15, 2); // gapok + total_lembur + total_tunjangan

            // Komponen Potongan (dihitung dari persentase gapok)
            $table->decimal('potongan_bpjs_kesehatan', 15, 2)->default(0);
            $table->decimal('potongan_bpjs_tenagakerja', 15, 2)->default(0);
            $table->decimal('potongan_lainnya', 15, 2)->default(0);
            $table->decimal('total_potongan', 15, 2);

            // PPH21 (jika ada)
            $table->decimal('pph21', 15, 2)->default(0);

            // Hasil Akhir
            $table->decimal('gaji_bersih', 15, 2);

            // Status dan metadata
            $table->enum('status', ['Belum Dibayar', 'Telah Dibayar'])->default('Belum Dibayar');
            $table->string('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('karyawan_id')
                ->references('id')
                ->on('tb_karyawan')
                ->onDelete('cascade');

            // Unique constraint
            $table->unique(['karyawan_id', 'bulan', 'tahun'], 'unique_gaji_per_periode');

            // Indexes
            $table->index('bulan');
            $table->index('tahun');
            $table->index(['bulan', 'tahun']);
            $table->index('status');
            $table->index(['karyawan_id', 'bulan', 'tahun']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_gaji_karyawan');
    }
};
