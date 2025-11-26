<?php
// app/DTO/PendapatanKaryawan/PendapatanDTO.php

namespace App\DTO\PendapatanKaryawan;

use App\DTO\Karyawan\KaryawanDTO;
use Carbon\Carbon;

class PendapatanKaryawanDTO
{
  public function __construct(
    public string $id,
    public string $karyawan_id,
    public float $tunjangan,
    public string $periode,
    public float $total_pendapatan,
    public string $created_at,
    public string $updated_at,
    public ?KaryawanDTO $karyawan = null,
    public float $total_lembur_perperiode,
    public float $total_pendapatan_lembur_perperiode
  ) {}

  /**
   * Convert from Model to DTO
   */
  public static function fromModel(object $pendapatan, array $lemburDetail = []): self
  {
    $karyawanDTO = null;
    if ($pendapatan->relationLoaded('karyawan') && $pendapatan->karyawan) {
      $karyawanDTO = KaryawanDTO::fromModel($pendapatan->karyawan);
    }

    return new self(
      id: $pendapatan->id,
      karyawan_id: $pendapatan->karyawan_id,
      tunjangan: (float) $pendapatan->tunjangan,
      periode: Carbon::parse($pendapatan->periode)->toISOString(),
      total_pendapatan: (float) $pendapatan->total_pendapatan,
      created_at: Carbon::parse($pendapatan->created_at)->toISOString(),
      updated_at: Carbon::parse($pendapatan->updated_at)->toISOString(),
      karyawan: KaryawanDTO::fromModel($pendapatan->karyawan),
      total_lembur_perperiode: (float) ($lemburDetail['total_lembur'] ?? 0),
      total_pendapatan_lembur_perperiode: (float) ($lemburDetail['total_lembur'] ?? 0)
    );
  }

  /**
   * Convert DTO to array
   */
  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'karyawan_id' => $this->karyawan_id,
      'tunjangan' => $this->tunjangan,
      'periode' => $this->periode,
      'total_pendapatan' => $this->total_pendapatan,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'karyawan' => $this->karyawan->toArray(),
      'total_lembur_perperiode' => $this->total_lembur_perperiode,
      'total_pendapatan_lembur_perperiode' => $this->total_pendapatan_lembur_perperiode
    ];
  }
}
