<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tb_lembur_karyawan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('tanggal');
            $table->string('hari');
            $table->uuid('karyawan_id');
            $table->decimal('jam_lembur', 5, 2)->default(0);
            $table->decimal('total_jam_lembur', 5, 2)->default(0);
            $table->decimal('upah_lembur_perjam', 15, 2);
            //   ->storedAs('(SELECT gapok FROM tb_karyawan WHERE id = karyawan_id) / 173');   INI TIDAK SUPPORT DI MYSQL JADI RUMUS DITARUH DI PayrollCalculationService.php
            $table->decimal('rupiah_lembur', 15, 2);
            $table->string('keterangan')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('karyawan_id')
                ->references('id')
                ->on('tb_karyawan')
                ->onDelete('cascade');

            // Indexes
            $table->index('tanggal');
            $table->index('karyawan_id');
            $table->index(['karyawan_id', 'tanggal']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_lembur_karyawan');
    }
};
