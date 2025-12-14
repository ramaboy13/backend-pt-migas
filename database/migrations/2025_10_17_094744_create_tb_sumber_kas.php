<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tb_sumber_kas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('tipe', ['BANK', 'CASH']);
            $table->string('nama_bank')->nullable(); // Hanya jika tipe=BANK
            $table->string('nomor_rekening')->nullable(); // Hanya jika tipe=BANK
            $table->string('atas_nama')->nullable(); // Hanya jika tipe=BANK
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->decimal('saldo_terakhir', 15, 2)->default(0);
            $table->boolean('aktif')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('tipe');
            $table->index('aktif');
            $table->unique(['nama_bank', 'nomor_rekening'], 'unique_bank_account');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_sumber_kas');
    }
};