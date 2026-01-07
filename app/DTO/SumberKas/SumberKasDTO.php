<?php

namespace App\DTO\SumberKas;

use App\Models\SumberKas;

class SumberKasDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $tipe,
        public readonly ?string $nama_bank,
        public readonly ?string $nomor_rekening,
        public readonly ?string $atas_nama,
        public readonly float $saldo_awal,
        public readonly float $saldo_terakhir,
        public readonly bool $aktif,
        public readonly ?string $keterangan,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?string $deletedAt,
        public readonly string $nama_display
    ) {}

    public static function fromModel(SumberKas $model): self
    {
        $nama_display = $model->tipe === 'CASH'
            ? 'Cash'
            : ($model->nama_bank ? "{$model->nama_bank} - {$model->nomor_rekening}" : 'Bank');

        return new self(
            id: $model->id,
            tipe: $model->tipe,
            nama_bank: $model->nama_bank,
            nomor_rekening: $model->nomor_rekening,
            atas_nama: $model->atas_nama,
            saldo_awal: (float) $model->saldo_awal,
            saldo_terakhir: (float) $model->saldo_terakhir,
            aktif: (bool) $model->aktif,
            keterangan: $model->keterangan,
            createdAt: $model->created_at->toISOString(),
            updatedAt: $model->updated_at->toISOString(),
            deletedAt: $model->deleted_at?->toISOString(),
            nama_display: $nama_display
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tipe' => $this->tipe,
            'nama_bank' => $this->nama_bank,
            'nomor_rekening' => $this->nomor_rekening,
            'atas_nama' => $this->atas_nama,
            'saldo_awal' => $this->saldo_awal,
            'saldo_terakhir' => $this->saldo_terakhir,
            'aktif' => $this->aktif,
            'keterangan' => $this->keterangan,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'nama_display' => $this->nama_display,
        ];
    }
}
