<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tb_pendapatan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('karyawan_id');
            $table->decimal('tunjangan', 15, 2)->default(0);
            $table->date('periode');
            $table->decimal('total_pendapatan', 15, 2);
                //   ->storedAs('
                //     (SELECT gapok FROM tb_karyawan WHERE id = karyawan_id) 
                //     + COALESCE((SELECT SUM(rupiah_lembur) FROM tb_lembur_karyawan WHERE karyawan_id = tb_pendapatan.karyawan_id AND MONTH(tanggal) = MONTH(periode) AND YEAR(tanggal) = YEAR(periode)), 0)
                //     + tunjangan
                //   ');
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('karyawan_id')
                  ->references('id')
                  ->on('tb_karyawan')
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
        Schema::dropIfExists('tb_pendapatan');
    }
};