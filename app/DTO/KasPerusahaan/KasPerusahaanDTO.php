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
        public readonly float $saldo_sebelum,
        public readonly float $saldo_sesudah,
        public readonly ?TransaksiOperasionalDTO $transaksi_operasional,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    public static function fromModel(\App\Models\KasPerusahaan $model): self
    {
        $transaksiOperasionalDTO = null;
        if ($model->relationLoaded('transaksiOperasional') && $model->transaksiOperasional) {
            $transaksiOperasionalDTO = \App\DTO\TransaksiOperasional\TransaksiOperasionalDTO::fromModel(
                $model->transaksiOperasional
            );
        }

        return new self(
            id: $model->id,
            tanggal: $model->tanggal->toDateString(),
            sumber_kas_id: $model->sumber_kas_id,
            keterangan: $model->keterangan,
            tipe_transaksi: $model->tipe_transaksi,
            jumlah: (float) $model->jumlah,
            saldo_sebelum: (float) $model->saldo_sebelum,
            saldo_sesudah: (float) $model->saldo_sesudah,
            transaksi_operasional: $transaksiOperasionalDTO,
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
            'saldo_sebelum' => $this->saldo_sebelum,
            'saldo_sesudah' => $this->saldo_sesudah,
            'transaksi_operasional' => $this->transaksi_operasional?->toArray(),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
