<?php

namespace App\DTO\Karyawan;

use Carbon\Carbon;

class KaryawanDTO
{
  public function __construct(
    public string $id,
    public int $nik,
    public string $nama,
    public string $jabatan,
    public float $gapok,
    public float $bpjs_kesehatan,
    public float $bpjs_tenagakerja,
    public string $tgl_masuk,
    public bool $is_active,
    public ?string $alamat,
    public string $created_at,
    public string $updated_at
  ) {}

  /**
   * Convert from Model to DTO
   */
  public static function fromModel(object $karyawan): self
  {
    return new self(
      id: $karyawan->id,
      nik: (int)$karyawan->NIK,
      nama: $karyawan->nama,
      jabatan: $karyawan->jabatan,
      gapok: (float) $karyawan->gapok,
      bpjs_kesehatan: (float) $karyawan->bpjs_kesehatan,
      bpjs_tenagakerja: (float) $karyawan->bpjs_tenagakerja,
      tgl_masuk: Carbon::parse($karyawan->tgl_masuk)->toISOString(),
      is_active: (bool) $karyawan->is_active,
      alamat: $karyawan->alamat,
      created_at: Carbon::parse($karyawan->created_at)->toISOString(),
      updated_at: Carbon::parse($karyawan->updated_at)->toISOString()
    );
  }

  /**
   * Convert DTO to array
   */
  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'nik' => $this->nik,
      'nama' => $this->nama,
      'jabatan' => $this->jabatan,
      'gapok' => $this->gapok,
      'bpjs_kesehatan' => $this->bpjs_kesehatan,
      'bpjs_tenagakerja' => $this->bpjs_tenagakerja,
      'tgl_masuk' => $this->tgl_masuk,
      'is_active' => $this->is_active,
      'alamat' => $this->alamat,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at
    ];
  }
}
