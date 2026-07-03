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

    public function getDashboard(): DashboardDTO
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super_admin');

        // Cache summary data for 10 minutes
        $summaryData = Cache::remember('dashboard_summary', 600, function () {
            return [
                'totalSaldoKas' => $this->repository->getTotalSaldoKas(),
                'totalPemasukanBulanIni' => $this->repository->getTotalPemasukanBulanIni(),
                'totalPengeluaranBulanIni' => $this->repository->getTotalPengeluaranBulanIni(),
                'totalAsset' => $this->repository->getTotalAsset(),
            ];
        });

        // Add super admin specific data
        $userRoleData = null;
        if ($isSuperAdmin) {
            $summaryData['totalUsers'] = Cache::remember('dashboard_total_users', 600, fn() => $this->repository->getTotalUsers());

            $userRoleData = Cache::remember('dashboard_user_role_stats', 600, function () {
                return [
                    'stats_by_role' => $this->repository->getUserStatsByRole(),
                ];
            });
        }

        $summary = new DashboardSummaryDTO(...$summaryData);

        // Get chart data, cache for 10 minutes
        $chartsData = Cache::remember('dashboard_charts', 600, function () {
            return [
                'dailyTransactions' => $this->repository->getDailyTransactions(7),
                'transactionByType' => $this->repository->getTransactionByType(),
                'saldoPerSumberKas' => $this->repository->getSaldoPerSumberKas()
            ];
        });

        $charts = new DashboardChartDTO(
            dailyTransactions: $chartsData['dailyTransactions'],
            transactionByType: $chartsData['transactionByType'],
            saldoPerSumberKas: $chartsData['saldoPerSumberKas']
        );

        // Get recent activities (maybe shorter cache, e.g. 1 minute, or no cache)
        $recentTransactions = Cache::remember('dashboard_recent_trans', 60, fn() => $this->repository->getRecentTransactions(10));
        $recentUsers = $isSuperAdmin ? Cache::remember('dashboard_recent_users', 60, fn() => $this->repository->getRecentUsers(10)) : null;

        $recentActivities = new RecentActivityDTO(
            recentTransactions: $recentTransactions,
            recentUsers: $recentUsers
        );
        
        $recentKasPerusahaan = Cache::remember('dashboard_recent_kas', 60, fn() => $this->repository->getRecentKasPerusahaan(10));

        return new DashboardDTO(
            summary: $summary->toArray(),
            charts: $charts->toArray(),
            recentActivities: $recentActivities->toArray(),
            userRole: $userRoleData,
            recentKasPerusahaan: $recentKasPerusahaan
        );
    }

    /**
     * Get summary only (for widget refresh)
     */
    public function getDashboardSummary(): array
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super_admin');

        $summaryData = Cache::remember('dashboard_summary_widget', 600, function () {
            $pemasukan = $this->repository->getTotalPemasukanBulanIni();
            $pengeluaran = $this->repository->getTotalPengeluaranBulanIni();
            
            return [
                'total_saldo_kas' => $this->repository->getTotalSaldoKas(),
                'total_pemasukan_bulan_ini' => $pemasukan,
                'total_pengeluaran_bulan_ini' => $pengeluaran,
                'net_cashflow' => $pemasukan - $pengeluaran,
                'total_karyawan' => $this->repository->getTotalKaryawan(),
                'total_asset' => $this->repository->getTotalAsset(),
            ];
        });

        if ($isSuperAdmin) {
            $summaryData['total_users'] = Cache::remember('dashboard_total_users', 600, fn() => $this->repository->getTotalUsers());
            $summaryData['new_users_this_month'] = Cache::remember('dashboard_new_users', 600, fn() => $this->repository->getNewUsersThisMonth());
        }

        return $summaryData;
    }

    /**
     * Get chart data only (for chart refresh)
     */
    public function getDashboardCharts(): array
    {
        return Cache::remember('dashboard_charts_widget', 600, function () {
            return [
                'daily_transactions' => $this->repository->getDailyTransactions(7),
                'transaction_by_type' => $this->repository->getTransactionByType(),
                'saldo_per_sumber_kas' => $this->repository->getSaldoPerSumberKas(),
            ];
        });
    }
}
