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
        Schema::create('table_pangkalan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('regist_id')->unique();
            $table->string('name');
            $table->string('no_ktp')->unique();
            $table->text('alamat')->nullable();
            $table->timestamps();
            $table->decimal('harga_satuan', 10, 6)->nullable();
            $table->index('regist_id');
            $table->index('no_ktp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_pangkalan');
    }
};
