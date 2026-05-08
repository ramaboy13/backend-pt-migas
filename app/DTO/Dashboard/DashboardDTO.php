<?php

namespace App\DTO\Dashboard;

class DashboardDTO
{
    public function __construct(
        public readonly array $summary,
        public readonly array $charts,
        public readonly array $recentActivities,
        public readonly ?array $userRole,
        public readonly array $recentKasPerusahaan,
    ) {}

    public function toArray(): array
    {
        $data = [
            'summary' => $this->summary,
            'charts' => $this->charts,
            'recent_activities' => $this->recentActivities,
            'recent_kas_perusahaan' => $this->recentKasPerusahaan,
        ];

        if ($this->userRole !== null) {
            $data['user_role'] = $this->userRole;
        }

        return $data;
    }
}
