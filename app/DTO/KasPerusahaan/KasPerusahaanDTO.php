<?php

namespace App\DTO\KasPerusahaan;

use App\DTO\SumberKas\SumberKasDTO;
use App\DTO\TransaksiOperasional\TransaksiOperasionalDTO;

class KasPerusahaanDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $tanggal,
        public readonly string $sumberKasId,
        public readonly string $keterangan,
        public readonly string $tipeTransaksi,
        public readonly float $jumlah,
        public readonly ?string $transaksiOperasionalId,
        public readonly float $saldoSebelum,
        public readonly float $saldoSesudah,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?SumberKasDTO $sumberKas = null,
        public readonly ?TransaksiOperasionalDTO $transaksiOperasional = null
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
            transaksiOperasionalId: $model->transaksi_operasional_id,
            saldoSebelum: (float) $model->saldo_sebelum,
            saldoSesudah: (float) $model->saldo_sesudah,
            createdAt: $model->created_at->toISOString(),
            updatedAt: $model->updated_at->toISOString(),
            sumberKas: $model->relationLoaded('sumberKas') ? SumberKasDTO::fromModel($model->sumberKas) : null,
            transaksiOperasional: $model->relationLoaded('transaksiOperasional') ? TransaksiOperasionalDTO::fromModel($model->transaksiOperasional) : null
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
            'transaksi_operasional_id' => $this->transaksiOperasionalId,
            'saldo_sebelum' => $this->saldoSebelum,
            'saldo_sesudah' => $this->saldoSesudah,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'sumber_kas' => $this->sumberKas?->toArray(),
            'transaksi_operasional' => $this->transaksiOperasional?->toArray(),
        ];
    }
}
