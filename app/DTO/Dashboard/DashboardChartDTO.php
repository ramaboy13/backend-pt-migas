<?php

namespace App\DTO\Dashboard;

class DashboardChartDTO
{
    public function __construct(
        public readonly array $dailyTransactions,
        public readonly array $transactionByType,
        public readonly array $saldoPerSumberKas
    ) {}

    public function toArray(): array
    {
        return [
            'daily_transactions' => $this->dailyTransactions,
            'transaction_by_type' => $this->transactionByType,
            'saldo_per_sumber_kas' => $this->saldoPerSumberKas
        ];
    }
}