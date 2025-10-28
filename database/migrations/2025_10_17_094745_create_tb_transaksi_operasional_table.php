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
            $table->string('keterangan');
            $table->uuid('pangkalan_id');
            $table->uuid('tabung_id');
            $table->boolean('is_in')->default(true); // true = masuk, false = keluar
            $table->integer('qty')->default(0);
            $table->string('unit');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->storedAs('qty * harga_satuan');

            $table->timestamps();

            // Foreign keys
            $table->foreign('pangkalan_id')
                ->references('id')
                ->on('tb_pangkalan')
                ->onDelete('cascade');

            $table->foreign('tabung_id')
                ->references('id')
                ->on('tb_tabung')
                ->onDelete('cascade');

            // Indexes
            $table->index('tanggal');
            $table->index('no_ref');
            $table->index(['pangkalan_id', 'tabung_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_transaksi_operasional');
    }
};
