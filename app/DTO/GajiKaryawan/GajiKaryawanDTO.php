<?php

namespace App\DTO\GajiKaryawan;

use App\DTO\Karyawan\KaryawanDTO;

class GajiKaryawanDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $karyawanId,
        public readonly int $bulan,
        public readonly int $tahun,
        public readonly string $tanggalGaji,
        public readonly float $gapok,
        public readonly float $totalLembur,
        public readonly float $totalTunjangan,
        public readonly float $totalPendapatan,
        public readonly float $potonganBpjsKesehatan,
        public readonly float $potonganBpjsTenagakerja,
        public readonly float $potonganLainnya,
        public readonly float $totalPotongan,
        public readonly float $pph21,
        public readonly float $gajiBersih,
        public readonly string $status,
        public readonly ?string $processedBy,
        public readonly ?string $processedAt,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?KaryawanDTO $karyawan = null,
        public readonly ?array $komponenLembur = null,
        public readonly ?array $komponenTunjangan = null
    ) {}

    public static function fromModel(\App\Models\GajiKaryawan $model, bool $withRelations = true): self
    {
        $karyawanDTO = null;
        $komponenLembur = null;
        $komponenTunjangan = null;

        if ($withRelations) {
            if ($model->relationLoaded('karyawan') && $model->karyawan) {
                $karyawanDTO = KaryawanDTO::fromModel($model->karyawan);
            }

            if ($model->relationLoaded('komponenGajiLembur')) {
                $komponenLembur = $model->komponenGajiLembur->map(fn ($item) => [
                    'id' => $item->id,
                    'tanggal' => $item->tanggal->toDateString(),
                    'jam_lembur' => (float) $item->jam_lembur,
                    'total_jam_lembur' => (float) $item->total_jam_lembur,
                    'nominal' => (float) $item->nominal,
                ])->toArray();
            }

            if ($model->relationLoaded('komponenGajiTunjangan')) {
                $komponenTunjangan = $model->komponenGajiTunjangan->map(fn ($item) => [
                    'id' => $item->id,
                    'tanggal' => $item->tanggal->toDateString(),
                    'nominal' => (float) $item->nominal,
                    'keterangan' => $item->keterangan,
                ])->toArray();
            }
        }

        return new self(
            id: $model->id,
            karyawanId: $model->karyawan_id,
            bulan: $model->bulan,
            tahun: $model->tahun,
            tanggalGaji: $model->tanggal_gaji->toDateString(),
            gapok: (float) $model->gapok,
            totalLembur: (float) $model->total_lembur,
            totalTunjangan: (float) $model->total_tunjangan,
            totalPendapatan: (float) $model->total_pendapatan,
            potonganBpjsKesehatan: (float) $model->potongan_bpjs_kesehatan,
            potonganBpjsTenagakerja: (float) $model->potongan_bpjs_tenagakerja,
            potonganLainnya: (float) $model->potongan_lainnya,
            totalPotongan: (float) $model->total_potongan,
            pph21: (float) $model->pph21,
            gajiBersih: (float) $model->gaji_bersih,
            status: $model->status,
            processedBy: $model->processed_by,
            processedAt: $model->processed_at?->toISOString(),
            createdAt: $model->created_at->toISOString(),
            updatedAt: $model->updated_at->toISOString(),
            karyawan: $karyawanDTO,
            komponenLembur: $komponenLembur,
            komponenTunjangan: $komponenTunjangan
        );
    }

    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'karyawan_id' => $this->karyawanId,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'tanggal_gaji' => $this->tanggalGaji,
            'gapok' => $this->gapok,
            'total_lembur' => $this->totalLembur,
            'total_tunjangan' => $this->totalTunjangan,
            'total_pendapatan' => $this->totalPendapatan,
            'potongan_bpjs_kesehatan' => $this->potonganBpjsKesehatan,
            'potongan_bpjs_tenagakerja' => $this->potonganBpjsTenagakerja,
            'potongan_lainnya' => $this->potonganLainnya,
            'total_potongan' => $this->totalPotongan,
            'pph21' => $this->pph21,
            'gaji_bersih' => $this->gajiBersih,
            'status' => $this->status,
            'processed_by' => $this->processedBy,
            'processed_at' => $this->processedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];

        if ($this->karyawan) {
            $data['karyawan'] = $this->karyawan->toArray();
        }

        if ($this->komponenLembur) {
            $data['detail_lembur'] = $this->komponenLembur;
        }

        if ($this->komponenTunjangan) {
            $data['detail_tunjangan'] = $this->komponenTunjangan;
        }

        return $data;
    }
}
