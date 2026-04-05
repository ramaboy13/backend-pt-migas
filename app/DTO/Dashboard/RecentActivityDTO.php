<?php

namespace App\DTO\Dashboard;

class RecentActivityDTO
{
    public function __construct(
        public readonly array $recentTransactions,
        public readonly ?array $recentUsers = null
    ) {}

    public function toArray(): array
    {
        $data = [
            'recent_transactions' => $this->recentTransactions
        ];

        if ($this->recentUsers !== null && !empty($this->recentUsers)) {
            $data['recent_users'] = $this->recentUsers;
        }

        return $data;
    }
}