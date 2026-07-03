<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tb_transaksi_operasional', function (Blueprint $table) {
            $table->index('tanggal');
            $table->index('pangkalan_id');
            $table->index('tabung_id');
            $table->index('kas_perusahaan_id');
            $table->index('is_pemasukan');
        });

        Schema::table('tb_gaji_karyawan', function (Blueprint $table) {
            $table->index('karyawan_id');
            $table->index('komponen_gaji_id');
            $table->index('tanggal');
        });

        Schema::table('tb_kas_perusahaan', function (Blueprint $table) {
            $table->index('sumber_kas_id');
        });

        Schema::table('tb_asset', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('tb_karyawan', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('status_aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_transaksi_operasional', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['pangkalan_id']);
            $table->dropIndex(['tabung_id']);
            $table->dropIndex(['kas_perusahaan_id']);
            $table->dropIndex(['is_pemasukan']);
        });

        Schema::table('tb_gaji_karyawan', function (Blueprint $table) {
            $table->dropIndex(['karyawan_id']);
            $table->dropIndex(['komponen_gaji_id']);
            $table->dropIndex(['tanggal']);
        });

        Schema::table('tb_kas_perusahaan', function (Blueprint $table) {
            $table->dropIndex(['sumber_kas_id']);
        });

        Schema::table('tb_asset', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('tb_karyawan', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status_aktif']);
        });
    }
};
