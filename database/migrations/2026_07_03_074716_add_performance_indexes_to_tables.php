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
        $indexes = \Illuminate\Support\Facades\DB::select('SHOW INDEXES FROM tb_transaksi_operasional WHERE Key_name = "tb_transaksi_operasional_tanggal_index"');
        if (empty($indexes)) {
            Schema::table('tb_transaksi_operasional', function (Blueprint $table) {
                $table->index('tanggal');
            });
        }

        $indexesGaji = \Illuminate\Support\Facades\DB::select('SHOW INDEXES FROM tb_gaji_karyawan WHERE Key_name = "tb_gaji_karyawan_tanggal_gaji_index"');
        if (empty($indexesGaji)) {
            Schema::table('tb_gaji_karyawan', function (Blueprint $table) {
                $table->index('tanggal_gaji');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = \Illuminate\Support\Facades\DB::select('SHOW INDEXES FROM tb_transaksi_operasional WHERE Key_name = "tb_transaksi_operasional_tanggal_index"');
        if (!empty($indexes)) {
            Schema::table('tb_transaksi_operasional', function (Blueprint $table) {
                $table->dropIndex('tb_transaksi_operasional_tanggal_index');
            });
        }

        $indexesGaji = \Illuminate\Support\Facades\DB::select('SHOW INDEXES FROM tb_gaji_karyawan WHERE Key_name = "tb_gaji_karyawan_tanggal_gaji_index"');
        if (!empty($indexesGaji)) {
            Schema::table('tb_gaji_karyawan', function (Blueprint $table) {
                $table->dropIndex('tb_gaji_karyawan_tanggal_gaji_index');
            });
        }
    }
};
