<?php

namespace App\DTO\LemburKaryawan;

use Carbon\Carbon;
use App\DTO\Karyawan\KaryawanDTO;

class LemburKaryawanDTO
{
  public function __construct(
    public string $id,
    public string $tanggal,
    public string $hari,
    public string $karyawan_id,
    public float $jam_lembur,
    public float $total_jam_lembur,
    public float $upah_lembur_perjam,
    public float $rupiah_lembur,
    public ?string $keterangan,
    public string $created_at,
    public string $updated_at,
    public ?KaryawanDTO $karyawan = null
  ) {}

  /**
   * Convert from Model to DTO
   */
  public static function fromModel(object $lembur): self
  {
    $karyawanDTO = null;
    if ($lembur->relationLoaded('karyawan') && $lembur->karyawan) {
      $karyawanDTO = KaryawanDTO::fromModel($lembur->karyawan);
    }

    return new self(
      id: $lembur->id,
      tanggal: Carbon::parse($lembur->tanggal)->toISOString(),
      hari: $lembur->hari,
      karyawan_id: $lembur->karyawan_id,
      jam_lembur: (float) $lembur->jam_lembur,
      total_jam_lembur: (float) $lembur->total_jam_lembur,
      upah_lembur_perjam: (float) $lembur->upah_lembur_perjam,
      rupiah_lembur: (float) $lembur->rupiah_lembur,
      keterangan: $lembur->keterangan,
      created_at: Carbon::parse($lembur->created_at)->toISOString(),
      updated_at: Carbon::parse($lembur->updated_at)->toISOString(),
      karyawan: $karyawanDTO
    );
  }

  /**
   * Convert DTO to array
   */
  public function toArray(): array
  {
    $data = [
      'id' => $this->id,
      'tanggal' => $this->tanggal,
      'hari' => $this->hari,
      'karyawan_id' => $this->karyawan_id,
      'jam_lembur' => $this->jam_lembur,
      'total_jam_lembur' => $this->total_jam_lembur,
      'upah_lembur_perjam' => $this->upah_lembur_perjam,
      'rupiah_lembur' => $this->rupiah_lembur,
      'keterangan' => $this->keterangan,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];

    // Include karyawan data if available
    if ($this->karyawan) {
      $data['karyawan'] = $this->karyawan->toArray();
    }

    return $data;
  }

  /**
   * Convert to array without relations (for performance)
   */
  public function toSimpleArray(): array
  {
    return [
      'id' => $this->id,
      'tanggal' => $this->tanggal,
      'hari' => $this->hari,
      'karyawan_id' => $this->karyawan_id,
      'jam_lembur' => $this->jam_lembur,
      'total_jam_lembur' => $this->total_jam_lembur,
      'upah_lembur_perjam' => $this->upah_lembur_perjam,
      'rupiah_lembur' => $this->rupiah_lembur,
      'keterangan' => $this->keterangan,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}
