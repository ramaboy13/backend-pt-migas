<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tb_kas_bca', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('tanggal');
            $table->string('keterangan');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            $table->decimal('saldo', 15, 2)->default(0);
            $table->decimal('saldo_akhir', 15, 2)->storedAs('saldo + debit - kredit');
            $table->timestamps();

            // Indexes
            $table->index('tanggal');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_kas_bca');
    }
};
