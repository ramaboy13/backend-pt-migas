<?php

namespace App\Repositories;

use App\Models\Asset;
use App\Models\Karyawan;
use App\Models\KasPerusahaan;
use App\Models\SumberKas;
use App\Models\TransaksiOperasional;
use App\Models\User;
use Carbon\Carbon;

class DashboardRepository
{
    /**
     * Get total saldo from all sumber kas
     */
    public function getTotalSaldoKas(): float
    {
        return (float) SumberKas::where('aktif', true)->sum('saldo_terakhir');
    }

    /**
     * Get total pemasukan bulan ini
     */
    public function getTotalPemasukanBulanIni(?string $bulan = null, ?string $tahun = null): float
    {
        $startOfMonth = $bulan && $tahun ? Carbon::create($tahun, $bulan, 1)->startOfMonth() : Carbon::now()->startOfMonth();
        $endOfMonth = $bulan && $tahun ? Carbon::create($tahun, $bulan, 1)->endOfMonth() : Carbon::now()->endOfMonth();

        return (float) KasPerusahaan::where('tipe_transaksi', 'DEBIT')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('jumlah');
    }

    /**
     * Get total pengeluaran bulan ini
     */
    public function getTotalPengeluaranBulanIni(?string $bulan = null, ?string $tahun = null): float
    {
        $startOfMonth = $bulan && $tahun ? Carbon::create($tahun, $bulan, 1)->startOfMonth() : Carbon::now()->startOfMonth();
        $endOfMonth = $bulan && $tahun ? Carbon::create($tahun, $bulan, 1)->endOfMonth() : Carbon::now()->endOfMonth();

        return (float) KasPerusahaan::where('tipe_transaksi', 'KREDIT')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('jumlah');
    }

    /**
     * Get total asset
     */
    public function getTotalAsset(): int
    {
        return Asset::count();
    }

    /**
     * Get daily transactions for last 7 days from selected month/year or now
     */
    public function getDailyTransactions(?string $bulan = null, ?string $tahun = null, int $days = 7): array
    {
        $result = [];
        
        $baseDate = $bulan && $tahun ? Carbon::create($tahun, $bulan, 1)->endOfMonth() : Carbon::now();
        if ($baseDate->isFuture()) {
            $baseDate = Carbon::now(); // Don't show future days if current month
        }

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $baseDate->copy()->subDays($i);
            $dateString = $date->format('Y-m-d');

            $pemasukan = (float) KasPerusahaan::where('tipe_transaksi', 'DEBIT')
                ->whereDate('tanggal', $dateString)
                ->sum('jumlah');

            $pengeluaran = (float) KasPerusahaan::where('tipe_transaksi', 'KREDIT')
                ->whereDate('tanggal', $dateString)
                ->sum('jumlah');

            $result[] = [
                'date' => $date->format('d M'),
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
                'net' => $pemasukan - $pengeluaran,
            ];
        }

        return $result;
    }

    /**
     * Get transaction summary by type for selected month/year
     */
    public function getTransactionByType(?string $bulan = null, ?string $tahun = null): array
    {
        $startOfMonth = $bulan && $tahun ? Carbon::create($tahun, $bulan, 1)->startOfMonth() : Carbon::now()->startOfMonth();
        $endOfMonth = $bulan && $tahun ? Carbon::create($tahun, $bulan, 1)->endOfMonth() : Carbon::now()->endOfMonth();

        $types = ['PEMBELIAN_GAS', 'PENJUALAN_PANGKALAN', 'PENJUALAN_GAS', 'MAINTENANCE', 'LAINNYA'];
        $result = [];

        foreach ($types as $type) {
            $total = (float) TransaksiOperasional::where('jenis_transaksi', $type)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->sum('jumlah');

            $result[] = [
                'type' => $type,
                'display' => $this->getJenisDisplay($type),
                'total' => $total,
            ];
        }

        return $result;
    }

    /**
     * Get saldo per sumber kas
     */
    public function getSaldoPerSumberKas(): array
    {
        return SumberKas::where('aktif', true)
            ->select('id', 'tipe', 'nama_bank', 'nomor_rekening', 'saldo_terakhir')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->tipe === 'CASH' ? 'Cash' : ($item->nama_bank ?? 'Bank').' - '.($item->nomor_rekening ?? ''),
                    'tipe' => $item->tipe,
                    'saldo' => (float) $item->saldo_terakhir,
                ];
            })
            ->toArray();
    }

    /**
     * Get recent transactions (last 10)
     */
    public function getRecentTransactions(int $limit = 10): array
    {
        return TransaksiOperasional::with(['pangkalan', 'tabung', 'asset', 'kasPerusahaan.sumberKas'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tanggal' => $item->tanggal->format('Y-m-d'),
                    'no_ref' => $item->no_ref,
                    'jenis_transaksi' => $item->getJenisDisplayAttribute(),
                    'keterangan' => $item->keterangan,
                    'is_pemasukan' => $item->is_pemasukan,
                    'jumlah' => (float) $item->jumlah,
                    'sumber_kas' => $item->kasPerusahaan?->sumberKas?->nama_bank ?? 'Cash',
                    'created_by' => $item->created_by,
                ];
            })
            ->toArray();
    }

    /**
     * Get recent kas perusahaan
     */
    public function getRecentKasPerusahaan(int $limit = 10): array
    {
        return KasPerusahaan::with('sumberKas')
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tanggal' => $item->tanggal->format('Y-m-d'),
                    'keterangan' => $item->keterangan,
                    'tipe_transaksi' => $item->tipe_transaksi,
                    'jumlah' => (float) $item->jumlah,
                    'saldo_sebelum' => (float) $item->saldo_sebelum,
                    'saldo_sesudah' => (float) $item->saldo_sesudah,
                    'sumber_kas' => $item->sumberKas
                        ? (
                            $item->sumberKas->tipe === 'CASH'
                                ? 'Cash'
                                : ($item->sumberKas->nama_bank ?? 'Bank')
                        )
                        : '-',
                    'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();
    }

    // ============ SUPER ADMIN ONLY METHODS ============

    /**
     * Get total users
     */
    public function getTotalUsers(): int
    {
        return User::count();
    }

    /**
     * Get recent users (last 10)
     */
    public function getRecentUsers(int $limit = 10): array
    {
        $users = User::with('roles')->orderBy('created_at', 'desc')->limit($limit)->get();

        $result = [];
        foreach ($users as $user) {
            // getRoleNames() returns array, not collection
            $roles = $user->getRoleNames();
            $role = ! empty($roles) ? $roles[0] : 'No role';

            $result[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role,
                'is_active' => (bool) $user->is_active,
                'email_verified' => isset($user->email_verified) ? (bool) $user->email_verified : false,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return $result;
    }

    /**
     * Get user statistics by role
     */
    public function getUserStatsByRole(): array
    {
        $users = User::with('roles')->get();
        $stats = [];

        foreach ($users as $user) {
            // getRoleNames() returns array, not collection
            $roles = $user->getRoleNames();
            $role = ! empty($roles) ? $roles[0] : 'unknown';

            if (! isset($stats[$role])) {
                $stats[$role] = 0;
            }
            $stats[$role]++;
        }

        return $stats;
    }

    private function getJenisDisplay(string $jenis): string
    {
        return match ($jenis) {
            'PEMBELIAN_GAS' => 'Pembelian Gas',
            'PENJUALAN_GAS' => 'Penjualan Gas',
            'PENJUALAN_PANGKALAN' => 'Penjualan Pangkalan',
            'MAINTENANCE' => 'Maintenance',
            'LAINNYA' => 'Lainnya',
            default => $jenis
        };
    }
}
