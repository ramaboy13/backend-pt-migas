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
        Schema::create('alamat_pangkalan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pangkalan_id');
            $table->string('no_kios');
            $table->string('desa');
            $table->string('kecamatan');
            $table->string('kabupaten');
            $table->string('provinsi');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
            
            $table->foreign('pangkalan_id')
                  ->references('id')
                  ->on('table_pangkalan')
                  ->onDelete('cascade');
                  
            $table->index('pangkalan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alamat_pangkalan');
    }
};
