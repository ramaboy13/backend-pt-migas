<?php

namespace App\DTO\TransaksiOperasional;

use App\DTO\KasPerusahaan\KasPerusahaanDTO;
use App\DTO\Pangkalan\PangkalanDTO;
use App\DTO\Tabung\TabungDTO;

class TransaksiOperasionalDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $tanggal,
        public readonly string $noRef,
        public readonly string $jenisTransaksi,
        public readonly string $keterangan,
        public readonly ?string $pangkalanId,
        public readonly ?string $tabungId,
        public readonly ?string $assetId,
        public readonly bool $isPemasukan,
        public readonly ?int $qty,
        public readonly ?string $unit,
        public readonly ?float $hargaSatuan,
        public readonly float $jumlah,
        public readonly ?string $kasPerusahaanId,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?string $deletedAt,
        public readonly string $jenisDisplay,
        public readonly string $status,
        public readonly ?PangkalanDTO $pangkalan = null,
        public readonly ?TabungDTO $tabung = null,
        public readonly ?KasPerusahaanDTO $kasPerusahaan = null
    ) {}

    public static function fromModel(\App\Models\TransaksiOperasional $model): self
    {
        return new self(
            id: $model->id,
            tanggal: $model->tanggal->toDateString(),
            noRef: $model->no_ref,
            jenisTransaksi: $model->jenis_transaksi,
            keterangan: $model->keterangan,
            pangkalanId: $model->pangkalan_id,
            tabungId: $model->tabung_id,
            assetId: $model->asset_id,
            isPemasukan: (bool) $model->is_pemasukan,
            qty: $model->qty,
            unit: $model->unit,
            hargaSatuan: $model->harga_satuan ? (float) $model->harga_satuan : null,
            jumlah: (float) $model->jumlah,
            kasPerusahaanId: $model->kas_perusahaan_id,
            createdAt: $model->created_at->toISOString(),
            updatedAt: $model->updated_at->toISOString(),
            deletedAt: $model->deleted_at?->toISOString(),
            jenisDisplay: $model->jenis_display,
            status: $model->status,
            pangkalan: $model->relationLoaded('pangkalan') && $model->pangkalan
                ? PangkalanDTO::fromModel($model->pangkalan)
                : null,
            tabung: $model->relationLoaded('tabung') && $model->tabung
                ? TabungDTO::fromModel($model->tabung)
                : null,
            kasPerusahaan: $model->relationLoaded('kasPerusahaan') && $model->kasPerusahaan
                ? KasPerusahaanDTO::fromModel($model->kasPerusahaan)
                : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal,
            'no_ref' => $this->noRef,
            'jenis_transaksi' => $this->jenisTransaksi,
            'jenis_display' => $this->jenisDisplay,
            'keterangan' => $this->keterangan,
            'pangkalan_id' => $this->pangkalanId,
            'tabung_id' => $this->tabungId,
            'asset_id' => $this->assetId,
            'is_pemasukan' => $this->isPemasukan,
            'status' => $this->status,
            'qty' => $this->qty,
            'unit' => $this->unit,
            'harga_satuan' => $this->hargaSatuan,
            'jumlah' => $this->jumlah,
            'kas_perusahaan_id' => $this->kasPerusahaanId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt,
            'pangkalan' => $this->pangkalan?->toArray(),
            'tabung' => $this->tabung?->toArray(),
            'kas_perusahaan' => $this->kasPerusahaan?->toArray(),
        ];
    }
}
