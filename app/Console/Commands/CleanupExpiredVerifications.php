<?php

namespace App\Console\Commands;

use App\Models\EmailVerification;
use Illuminate\Console\Command;

class CleanupExpiredVerifications extends Command
{
    protected $signature = 'verifications:cleanup';
    protected $description = 'Cleanup expired email verifications';

    public function handle(): int
    {
        $count = EmailVerification::where('expires_at', '<', now())
            ->orWhere('verified_at', '<', now()->subDays(7))
            ->delete();

        $this->info("Deleted {$count} expired verification records.");

        // Also cleanup expired refresh tokens
        $refreshTokens = \App\Models\RefreshToken::where('expires_at', '<', now())->delete();
        $this->info("Deleted {$refreshTokens} expired refresh tokens.");

        return 0;
    }
}