<?php

namespace App\DTO\KomponenGaji;

use App\DTO\Karyawan\KaryawanDTO;
use Carbon\Carbon;

class KomponenGajiDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $tipe,
        public readonly string $tipeDisplay,
        public readonly string $karyawanId,
        public readonly string $tanggal,
        public readonly ?float $jamLembur,
        public readonly ?float $totalJamLembur,
        public readonly ?float $upahPerjam,
        public readonly float $nominal,
        public readonly ?string $keterangan,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?string $deletedAt,
        public readonly ?KaryawanDTO $karyawan = null
    ) {}

    public static function fromModel(\App\Models\KomponenGaji $model, bool $withKaryawan = true): self
    {
        $karyawanDTO = null;

        if ($withKaryawan && $model->relationLoaded('karyawan') && $model->karyawan) {
            $karyawanDTO = KaryawanDTO::fromModel($model->karyawan);
        }

        return new self(
            id: $model->id,
            tipe: $model->tipe,
            tipeDisplay: $model->tipe_display,
            karyawanId: $model->karyawan_id,
            tanggal: Carbon::parse($model->tanggal)->toDateString(),
            jamLembur: $model->jam_lembur ? (float) $model->jam_lembur : null,
            totalJamLembur: $model->total_jam_lembur ? (float) $model->total_jam_lembur : null,
            upahPerjam: $model->upah_perjam ? (float) $model->upah_perjam : null,
            nominal: (float) $model->nominal,
            keterangan: $model->keterangan,
            createdAt: $model->created_at->toISOString(),
            updatedAt: $model->updated_at->toISOString(),
            deletedAt: $model->deleted_at?->toISOString(),
            karyawan: $karyawanDTO
        );
    }

    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'tipe' => $this->tipe,
            'tipe_display' => $this->tipeDisplay,
            'karyawan_id' => $this->karyawanId,
            'tanggal' => $this->tanggal,
            'nominal' => $this->nominal,
            'keterangan' => $this->keterangan,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];

        // Field lembur akan muncul jika tipe LEMBUR
        if ($this->tipe === 'LEMBUR') {
            $data['jam_lembur'] = $this->jamLembur;
            $data['total_jam_lembur'] = $this->totalJamLembur;
            $data['upah_perjam'] = $this->upahPerjam;
        }

        if ($this->karyawan) {
            $data['karyawan'] = $this->karyawan->toArray();
        }

        if ($this->deletedAt) {
            $data['deleted_at'] = $this->deletedAt;
        }

        return $data;
    }

    public function toSimpleArray(): array
    {
        return [
            'id' => $this->id,
            'tipe' => $this->tipe,
            'tipe_display' => $this->tipeDisplay,
            'karyawan_id' => $this->karyawanId,
            'tanggal' => $this->tanggal,
            'nominal' => $this->nominal,
            'keterangan' => $this->keterangan,
        ];
    }
}
