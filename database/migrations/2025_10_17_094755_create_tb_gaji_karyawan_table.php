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
            $table->uuid('pendapatan_id');
            $table->uuid('potongan_id');
            $table->decimal('pph21', 15, 2)->default(0);
            $table->date('periode');
            $table->decimal('subtotal', 15, 2)->default(0);
            //   ->storedAs('
            //     (SELECT total_pendapatan FROM tb_pendapatan WHERE id = pendapatan_id) 
            //     - (SELECT total_potongan FROM tb_potongan WHERE id = potongan_id)
            //   ');      AKAN DI HITUNG DI PayrollCalculationService.php
            $table->decimal('gaji_bersih', 15, 2);
            //   ->storedAs('subtotal - pph21'); AKAN DI HITUNG DI PayrollCalculationService.php

            $table->timestamps();

            // Foreign keys
            $table->foreign('karyawan_id')
                ->references('id')
                ->on('tb_karyawan')
                ->onDelete('cascade');

            $table->foreign('pendapatan_id')
                ->references('id')
                ->on('tb_pendapatan')
                ->onDelete('cascade');

            $table->foreign('potongan_id')
                ->references('id')
                ->on('tb_potongan')
                ->onDelete('cascade');

            // Unique constraint
            $table->unique(['karyawan_id', 'periode']);

            // Indexes
            $table->index('periode');
            $table->index(['karyawan_id', 'periode']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_gaji_karyawan');
    }
};
