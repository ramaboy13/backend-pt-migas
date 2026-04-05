<?php
// app/Services/DashboardService.php

namespace App\Services;

use App\Repositories\DashboardRepository;
use App\DTO\Dashboard\DashboardDTO;
use App\DTO\Dashboard\DashboardSummaryDTO;
use App\DTO\Dashboard\DashboardChartDTO;
use App\DTO\Dashboard\RecentActivityDTO;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function __construct(
        private DashboardRepository $repository
    ) {}

    public function getDashboard(): DashboardDTO
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super_admin');

        // Get summary data
        $summaryData = [
            'totalSaldoKas' => $this->repository->getTotalSaldoKas(),
            'totalPemasukanBulanIni' => $this->repository->getTotalPemasukanBulanIni(),
            'totalPengeluaranBulanIni' => $this->repository->getTotalPengeluaranBulanIni(),
            'totalKaryawan' => $this->repository->getTotalKaryawan(),
            'totalAsset' => $this->repository->getTotalAsset(),
        ];

        // Calculate net cashflow
        $summaryData['netCashflow'] = $summaryData['totalPemasukanBulanIni'] - $summaryData['totalPengeluaranBulanIni'];

        // Add super admin specific data 
        $userRoleData = null;
        if ($isSuperAdmin) {
            $summaryData['totalUsers'] = $this->repository->getTotalUsers();
            $summaryData['newUsersThisMonth'] = $this->repository->getNewUsersThisMonth();
            
            $userRoleData = [
                'stats_by_role' => $this->repository->getUserStatsByRole()
            ];
        }

        $summary = new DashboardSummaryDTO(...$summaryData);

        // Get chart data
        $charts = new DashboardChartDTO(
            dailyTransactions: $this->repository->getDailyTransactions(7),
            transactionByType: $this->repository->getTransactionByType(),
            saldoPerSumberKas: $this->repository->getSaldoPerSumberKas()
        );

        // Get recent activities
        $recentTransactions = $this->repository->getRecentTransactions(10);
        $recentUsers = $isSuperAdmin ? $this->repository->getRecentUsers(10) : null;

        $recentActivities = new RecentActivityDTO(
            recentTransactions: $recentTransactions,
            recentUsers: $recentUsers
        );

        return new DashboardDTO(
            summary: $summary->toArray(),
            charts: $charts->toArray(),
            recentActivities: $recentActivities->toArray(),
            userRole: $userRoleData
        );
    }

    /**
     * Get summary only (for widget refresh)
     */
    public function getDashboardSummary(): array
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super_admin');

        $summaryData = [
            'total_saldo_kas' => $this->repository->getTotalSaldoKas(),
            'total_pemasukan_bulan_ini' => $this->repository->getTotalPemasukanBulanIni(),
            'total_pengeluaran_bulan_ini' => $this->repository->getTotalPengeluaranBulanIni(),
            'net_cashflow' => $this->repository->getTotalPemasukanBulanIni() - $this->repository->getTotalPengeluaranBulanIni(),
            'total_karyawan' => $this->repository->getTotalKaryawan(),
            'total_asset' => $this->repository->getTotalAsset()
        ];

        if ($isSuperAdmin) {
            $summaryData['total_users'] = $this->repository->getTotalUsers();
            $summaryData['new_users_this_month'] = $this->repository->getNewUsersThisMonth();
        }

        return $summaryData;
    }

    /**
     * Get chart data only (for chart refresh)
     */
    public function getDashboardCharts(): array
    {
        return [
            'daily_transactions' => $this->repository->getDailyTransactions(7),
            'transaction_by_type' => $this->repository->getTransactionByType(),
            'saldo_per_sumber_kas' => $this->repository->getSaldoPerSumberKas()
        ];
    }
}