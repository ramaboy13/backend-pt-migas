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
        Schema::create('alamat_karyawan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('karyawan_id');
            $table->string('no_rumah');
            $table->boolean('is_kontrak')->default(false); // true = kontrak default = tidak
            $table->string('desa');
            $table->string('kecamatan');
            $table->string('kabupaten');
            $table->string('provinsi');
            $table->text('note')->nullable();
            $table->timestamps();
            
            $table->foreign('karyawan_id')
                  ->references('id')
                  ->on('tb_karyawan')
                  ->onDelete('cascade');
                  
            $table->index('karyawan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alamat_karyawan');
    }
};
