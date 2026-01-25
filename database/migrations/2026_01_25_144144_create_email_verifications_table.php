<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Str::uuid());
            $table->string('email')->index();
            $table->string('token')->unique();
            $table->string('verification_code', 6)->nullable();
            $table->uuid('user_id')->nullable();
            $table->enum('type', ['REGISTRATION', 'PASSWORD_RESET', 'EMAIL_CHANGE'])->default('REGISTRATION');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['email', 'token']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verifications');
    }
};