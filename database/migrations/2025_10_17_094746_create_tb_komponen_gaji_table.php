<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tb_komponen_gaji', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('tipe', ['LEMBUR', 'TUNJANGAN']);
            $table->uuid('karyawan_id');
            $table->date('tanggal');
            $table->decimal('jam_lembur', 5, 2)->nullable();
            $table->decimal('total_jam_lembur', 5, 2)->nullable();
            $table->decimal('upah_perjam', 15, 2)->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('karyawan_id')
                ->references('id')
                ->on('tb_karyawan')
                ->onDelete('cascade');

            // Indexes
            $table->index('tipe');
            $table->index('tanggal');
            $table->index('karyawan_id');
            $table->index(['karyawan_id', 'tanggal']);
            $table->index(['karyawan_id', 'tipe']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_komponen_gaji');
    }
};
