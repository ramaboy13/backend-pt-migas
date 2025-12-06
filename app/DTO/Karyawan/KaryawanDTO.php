<?php

namespace App\DTO\Karyawan;

use Carbon\Carbon;

class KaryawanDTO
{
    public function __construct(
        public string $id,
        public string $nik,
        public string $nama,
        public string $jabatan,
        public float $gaji_pokok,
        public float $bpjs_kesehatan,
        public float $bpjs_tenagakerja,
        public string $tgl_masuk,
        public bool $aktif,
        public ?string $alamat,
        public string $created_at,
        public string $updated_at
    ) {}

    public static function fromModel(object $karyawan): self
    {
        return new self(
            id: $karyawan->id,
            nik: $karyawan->NIK, 
            nama: $karyawan->nama,
            jabatan: $karyawan->jabatan,
            gaji_pokok: (float) $karyawan->gaji_pokok,
            bpjs_kesehatan: (float) $karyawan->bpjs_kesehatan,
            bpjs_tenagakerja: (float) $karyawan->bpjs_tenagakerja,
            tgl_masuk: Carbon::parse($karyawan->tgl_masuk)->toISOString(),
            aktif: (bool) $karyawan->aktif,
            alamat: $karyawan->alamat,
            created_at: Carbon::parse($karyawan->created_at)->toISOString(),
            updated_at: Carbon::parse($karyawan->updated_at)->toISOString()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nik' => $this->nik,
            'nama' => $this->nama,
            'jabatan' => $this->jabatan,
            'gaji_pokok' => $this->gaji_pokok,
            'bpjs_kesehatan' => $this->bpjs_kesehatan,
            'bpjs_tenagakerja' => $this->bpjs_tenagakerja,
            'tgl_masuk' => $this->tgl_masuk,
            'aktif' => $this->aktif,
            'alamat' => $this->alamat,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
