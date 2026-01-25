<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_codes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Str::uuid());
            $table->string('code', 20)->unique();
            $table->uuid('created_by');
            $table->uuid('used_by')->nullable();
            $table->string('role');
            $table->integer('max_uses')->default(1);
            $table->integer('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('used_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['code', 'is_active']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_codes');
    }
};