<?php

namespace App\DTO\KasPerusahaan;

class KasPerusahaanDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $tanggal,
        public readonly string $sumberKasId,
        public readonly string $keterangan,
        public readonly string $tipeTransaksi,
        public readonly float $jumlah,
        public readonly float $saldoSebelum,
        public readonly float $saldoSesudah,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    public static function fromModel(\App\Models\KasPerusahaan $model): self
    {
        return new self(
            id: $model->id,
            tanggal: $model->tanggal->toDateString(),
            sumberKasId: $model->sumber_kas_id,
            keterangan: $model->keterangan,
            tipeTransaksi: $model->tipe_transaksi,
            jumlah: (float) $model->jumlah,
            saldoSebelum: (float) $model->saldo_sebelum,
            saldoSesudah: (float) $model->saldo_sesudah,
            createdAt: $model->created_at->toISOString(),
            updatedAt: $model->updated_at->toISOString(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal,
            'sumber_kas_id' => $this->sumberKasId,
            'keterangan' => $this->keterangan,
            'tipe_transaksi' => $this->tipeTransaksi,
            'jumlah' => $this->jumlah,
            'saldo_sebelum' => $this->saldoSebelum,
            'saldo_sesudah' => $this->saldoSesudah,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
