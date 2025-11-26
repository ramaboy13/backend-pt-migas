<?php

namespace App\DTO\PotonganKaryawan;

use App\DTO\Karyawan\KaryawanDTO;
use Carbon\Carbon;

class PotonganKaryawanDTO
{
  public function __construct(
    public string $id,
    public string $karyawan_id,
    public string $periode,
    public float $rp_bpjs_kesehatan,
    public float $rp_bpjs_tenagakerja,
    public float $total_potongan,
    public string $created_at,
    public string $updated_at,
    public ?KaryawanDTO $karyawan = null
  ) {}

  /**
   * Convert from Model to DTO
   */
  public static function fromModel(object $potongan): self
  {
    $karyawanDTO = null;
    if ($potongan->relationLoaded('karyawan') && $potongan->karyawan) {
      $karyawanDTO = KaryawanDTO::fromModel($potongan->karyawan);
    }

    return new self(
      id: $potongan->id,
      karyawan_id: $potongan->karyawan_id,
      periode: Carbon::parse($potongan->periode)->format('Y-m-d'),
      rp_bpjs_kesehatan: (float) $potongan->rp_bpjs_kesehatan,
      rp_bpjs_tenagakerja: (float) $potongan->rp_bpjs_tenagakerja,
      total_potongan: (float) $potongan->total_potongan,
      created_at: Carbon::parse($potongan->created_at)->toISOString(),
      updated_at: Carbon::parse($potongan->updated_at)->toISOString(),
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
      'karyawan_id' => $this->karyawan_id,
      'periode' => $this->periode,
      'rp_bpjs_kesehatan' => $this->rp_bpjs_kesehatan,
      'rp_bpjs_tenagakerja' => $this->rp_bpjs_tenagakerja,
      'total_potongan' => $this->total_potongan,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at
    ];

    // Include karyawan data if available
    if ($this->karyawan) {
      $data['karyawan'] = $this->karyawan->toArray();
    }

    return $data;
  }
}
