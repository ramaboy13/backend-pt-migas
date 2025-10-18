<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tb_potongan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('karyawan_id');
            $table->date('periode'); 
            $table->decimal('rp_bpjs_kesehatan', 15, 2);
                //   ->storedAs('(SELECT gapok FROM tb_karyawan WHERE id = karyawan_id) * (SELECT bpjs_kesehatan FROM tb_karyawan WHERE id = karyawan_id) / 100');
                        
            $table->decimal('rp_bpjs_tenagakerja', 15, 2);
                //   ->storedAs('(SELECT gapok FROM tb_karyawan WHERE id = karyawan_id) * (SELECT bpjs_tenagakerja FROM tb_karyawan WHERE id = karyawan_id) / 100');
                  
            $table->decimal('total_potongan', 15, 2)
                  ->storedAs('rp_bpjs_kesehatan + rp_bpjs_tenagakerja');
            
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
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_potongan');
    }
};