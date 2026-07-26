<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tb_transaksi_operasional', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('tanggal');
            $table->string('no_ref')->unique();
            $table->enum('jenis_transaksi', ['PEMBELIAN_GAS', 'MAINTENANCE', 'PENJUALAN_GAS', 'LAINNYA']);
            $table->string('keterangan');


            // Bisa NULL karena tidak semua transaksi butuh
            $table->uuid('pangkalan_id')->nullable();
            $table->uuid('tabung_id')->nullable();
            $table->uuid('asset_id')->nullable();


            // Boolean untuk tipe transaksi
            $table->boolean('is_pemasukan')->default(false);

            // Detail transaksi
            $table->integer('qty')->nullable()->default(0);
            $table->string('unit')->nullable();
            $table->decimal('harga_satuan', 15, 2)->nullable()->default(0);
            $table->decimal('jumlah', 15, 2); // Jumlah uang transaksi

            // Link ke kas
            $table->uuid('kas_perusahaan_id')->nullable();
            $table->string('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('pangkalan_id')->references('id')->on('tb_pangkalan')->onDelete('set null');
            $table->foreign('tabung_id')->references('id')->on('tb_tabung')->onDelete('set null');
            $table->foreign('kas_perusahaan_id')->references('id')->on('tb_kas_perusahaan')->onDelete('set null');
            $table->foreign('asset_id', 'transaksi_operasional_asset_fk')->references('id')->on('tb_asset')->onDelete('cascade');

            // Indexes
            $table->index('jenis_transaksi');
            $table->index('is_pemasukan');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_transaksi_operasional');
    }
};
