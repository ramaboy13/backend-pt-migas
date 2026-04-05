<?php

namespace App\DTO\Dashboard;

class DashboardSummaryDTO
{
    public function __construct(
        public readonly float $totalSaldoKas,
        public readonly float $totalPemasukanBulanIni,
        public readonly float $totalPengeluaranBulanIni,
        public readonly float $netCashflow,
        public readonly int $totalKaryawan,
        public readonly int $totalAsset,
        public readonly ?int $totalUsers = null,      
        public readonly ?int $totalReferralCreated = null, 
        public readonly ?int $totalReferralUsed = null,    
        public readonly ?int $newUsersThisMonth = null     
    ) {}

    public function toArray(): array
    {
        $data = [
            'total_saldo_kas' => $this->totalSaldoKas,
            'total_pemasukan_bulan_ini' => $this->totalPemasukanBulanIni,
            'total_pengeluaran_bulan_ini' => $this->totalPengeluaranBulanIni,
            'net_cashflow' => $this->netCashflow,
            'total_karyawan' => $this->totalKaryawan,
            'total_asset' => $this->totalAsset
        ];

        // Add super admin specific data
        if ($this->totalUsers !== null) {
            $data['total_users'] = $this->totalUsers;
            $data['total_referral_created'] = $this->totalReferralCreated;
            $data['total_referral_used'] = $this->totalReferralUsed;
            $data['new_users_this_month'] = $this->newUsersThisMonth;
        }

        return $data;
    }
}