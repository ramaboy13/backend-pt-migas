<?php

namespace App\DTO\Dashboard;

class DashboardDTO
{
    public function __construct(
        public readonly array $summary,
        public readonly array $charts,
        public readonly array $recentActivities,
        public readonly ?array $userRole = null
    ) {}

    public function toArray(): array
    {
        $data = [
            'summary' => $this->summary,
            'charts' => $this->charts,
            'recent_activities' => $this->recentActivities
        ];

        if ($this->userRole !== null) {
            $data['user_role'] = $this->userRole;
        }

        return $data;
    }
}