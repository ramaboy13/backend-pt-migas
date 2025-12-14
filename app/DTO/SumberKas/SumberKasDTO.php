<?php

namespace App\DTO\SumberKas;

use App\Models\SumberKas;

class SumberKasDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $tipe,
        public readonly ?string $namaBank,
        public readonly ?string $nomorRekening,
        public readonly ?string $atasNama,
        public readonly float $saldoAwal,
        public readonly float $saldoTerakhir,
        public readonly bool $aktif,
        public readonly ?string $keterangan,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?string $deletedAt,
        public readonly string $namaDisplay
    ) {}

    public static function fromModel(SumberKas $model): self
    {
        $namaDisplay = $model->tipe === 'CASH'
            ? 'Cash'
            : ($model->nama_bank ? "{$model->nama_bank} - {$model->nomor_rekening}" : 'Bank');

        return new self(
            id: $model->id,
            tipe: $model->tipe,
            namaBank: $model->nama_bank,
            nomorRekening: $model->nomor_rekening,
            atasNama: $model->atas_nama,
            saldoAwal: (float) $model->saldo_awal,
            saldoTerakhir: (float) $model->saldo_terakhir,
            aktif: (bool) $model->aktif,
            keterangan: $model->keterangan,
            createdAt: $model->created_at->toISOString(),
            updatedAt: $model->updated_at->toISOString(),
            deletedAt: $model->deleted_at?->toISOString(),
            namaDisplay: $namaDisplay
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tipe' => $this->tipe,
            'nama_bank' => $this->namaBank,
            'nomor_rekening' => $this->nomorRekening,
            'atas_nama' => $this->atasNama,
            'saldo_awal' => $this->saldoAwal,
            'saldo_terakhir' => $this->saldoTerakhir,
            'aktif' => $this->aktif,
            'keterangan' => $this->keterangan,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt,
            'nama_display' => $this->namaDisplay,
        ];
    }
}
