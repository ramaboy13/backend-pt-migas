<?php

namespace App\DTO\KasPerusahaan;

use App\DTO\TransaksiOperasional\TransaksiOperasionalDTO;

class KasPerusahaanDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $tanggal,
        public readonly string $sumber_kas_id,
        public readonly string $keterangan,
        public readonly string $tipe_transaksi,
        public readonly float $jumlah,
        public readonly float $saldoSebelum,
        public readonly float $saldoSesudah,
        public readonly ?TransaksiOperasionalDTO $transaksiOperasional,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    public static function fromModel(\App\Models\KasPerusahaan $model): self
    {
        return new self(
            id: $model->id,
            tanggal: $model->tanggal->toDateString(),
            sumber_kas_id: $model->sumber_kas_id,
            keterangan: $model->keterangan,
            tipe_transaksi: $model->tipe_transaksi,
            jumlah: (float) $model->jumlah,
            saldoSebelum: (float) $model->saldo_sebelum,
            saldoSesudah: (float) $model->saldo_sesudah,
            transaksiOperasional: $model->transaksiOperasional
            ? \App\DTO\TransaksiOperasional\TransaksiOperasionalDTO::fromModel(
                $model->transaksiOperasional
            )
            : null,
            createdAt: $model->created_at->toISOString(),
            updatedAt: $model->updated_at->toISOString(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal,
            'sumber_kas_id' => $this->sumber_kas_id,
            'keterangan' => $this->keterangan,
            'tipe_transaksi' => $this->tipe_transaksi,
            'jumlah' => $this->jumlah,
            'saldo_sebelum' => $this->saldoSebelum,
            'saldo_sesudah' => $this->saldoSesudah,
            'transaksi_operasional' => $this->transaksiOperasional?->toArray(),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
