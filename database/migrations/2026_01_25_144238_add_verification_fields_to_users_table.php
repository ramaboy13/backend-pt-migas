<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_verified')->default(false)->after('email');
            $table->uuid('referred_by')->nullable()->after('email_verified');
            $table->string('referral_code_used', 20)->nullable()->after('referred_by');
            
            $table->foreign('referred_by')->references('id')->on('users')->onDelete('set null');
            $table->index('email_verified');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropColumn(['email_verified', 'referred_by', 'referral_code_used']);
        });
    }
};