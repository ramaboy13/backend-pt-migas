<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tb_kas_perusahaan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('tanggal');
            $table->uuid('sumber_kas_id');
            $table->string('keterangan');
            $table->enum('tipe_transaksi', ['DEBIT', 'KREDIT']);
            $table->decimal('jumlah', 15, 2);
            $table->uuid('transaksi_operasional_id')->nullable();
            $table->decimal('saldo_sebelum', 15, 2);
            $table->decimal('saldo_sesudah', 15, 2);
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('sumber_kas_id')
                  ->references('id')
                  ->on('tb_sumber_kas')
                  ->onDelete('restrict');
            
            // Indexes
            $table->index('tanggal');
            $table->index('sumber_kas_id');
            $table->index('tipe_transaksi');
            $table->index(['tanggal', 'sumber_kas_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_kas_perusahaan');
    }
};