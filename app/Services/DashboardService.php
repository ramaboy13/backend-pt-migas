<?php

namespace App\Services;

use App\DTO\Dashboard\DashboardChartDTO;
use App\DTO\Dashboard\DashboardDTO;
use App\DTO\Dashboard\DashboardSummaryDTO;
use App\DTO\Dashboard\RecentActivityDTO;
use App\Repositories\DashboardRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function __construct(
        private DashboardRepository $repository
    ) {}

    public function getDashboard(?string $bulan = null, ?string $tahun = null): DashboardDTO
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super_admin');

        $cacheKeySuffix = $bulan && $tahun ? "_{$bulan}_{$tahun}" : '_current';

        // Cache summary data for 10 minutes
        $summaryData = Cache::remember("dashboard_summary{$cacheKeySuffix}", 600, function () use ($bulan, $tahun) {
            return [
                'totalSaldoKas' => $this->repository->getTotalSaldoKas(),
                'totalPemasukanBulanIni' => $this->repository->getTotalPemasukanBulanIni($bulan, $tahun),
                'totalPengeluaranBulanIni' => $this->repository->getTotalPengeluaranBulanIni($bulan, $tahun),
                'totalAsset' => $this->repository->getTotalAsset(),
            ];
        });

        // Add super admin specific data
        $userRoleData = null;
        if ($isSuperAdmin) {
            $summaryData['totalUsers'] = Cache::remember("dashboard_total_users{$cacheKeySuffix}", 600, fn() => $this->repository->getTotalUsers());

            $userRoleData = Cache::remember("dashboard_user_role_stats{$cacheKeySuffix}", 600, function () {
                return [
                    'stats_by_role' => $this->repository->getUserStatsByRole(),
                ];
            });
        }

        $summary = new DashboardSummaryDTO(...$summaryData);

        // Get chart data, cache for 10 minutes
        $chartsData = Cache::remember("dashboard_charts{$cacheKeySuffix}", 600, function () use ($bulan, $tahun) {
            return [
                'dailyTransactions' => $this->repository->getDailyTransactions($bulan, $tahun, 7),
                'transactionByType' => $this->repository->getTransactionByType($bulan, $tahun),
                'saldoPerSumberKas' => $this->repository->getSaldoPerSumberKas()
            ];
        });

        $charts = new DashboardChartDTO(
            dailyTransactions: $chartsData['dailyTransactions'],
            transactionByType: $chartsData['transactionByType'],
            saldoPerSumberKas: $chartsData['saldoPerSumberKas']
        );

        // Get recent activities (maybe shorter cache, e.g. 1 minute, or no cache). Limit to 5
        $recentTransactions = Cache::remember("dashboard_recent_trans{$cacheKeySuffix}", 60, fn() => $this->repository->getRecentTransactions(5));
        $recentUsers = $isSuperAdmin ? Cache::remember("dashboard_recent_users{$cacheKeySuffix}", 60, fn() => $this->repository->getRecentUsers(5)) : null;

        $recentActivities = new RecentActivityDTO(
            recentTransactions: $recentTransactions,
            recentUsers: $recentUsers
        );
        
        $recentKasPerusahaan = Cache::remember("dashboard_recent_kas{$cacheKeySuffix}", 60, fn() => $this->repository->getRecentKasPerusahaan(5));

        return new DashboardDTO(
            summary: $summary->toArray(),
            charts: $charts->toArray(),
            recentActivities: $recentActivities->toArray(),
            userRole: $userRoleData,
            recentKasPerusahaan: $recentKasPerusahaan
        );
    }

}
