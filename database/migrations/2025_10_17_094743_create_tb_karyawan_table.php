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
        Schema::create('tb_karyawan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('NIK', 16)->unique();
            $table->string('nama');
            $table->string('jabatan');
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('bpjs_kesehatan', 5, 2)->default(0); // percentage
            $table->decimal('bpjs_tenagakerja', 5, 2)->default(0); // percentage
            $table->date('tgl_masuk');
            $table->boolean('aktif')->default(true); // true = aktif, false = tidak
            $table->longText('alamat')->nullable();
            $table->timestamps();
            //Indexes
            $table->index('NIK');
            $table->index('aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_karyawan');
    }
};
