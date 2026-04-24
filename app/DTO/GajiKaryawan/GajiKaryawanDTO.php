<?php
// app/DTO/GajiKaryawan/GajiKaryawanDTO.php

namespace App\DTO\GajiKaryawan;

use App\DTO\Karyawan\KaryawanDTO;
use App\DTO\PendapatanKaryawan\PendapatanKaryawanDTO;
use App\DTO\PotonganKaryawan\PotonganKaryawanDTO;
use Carbon\Carbon;

class GajiKaryawanDTO
{
  public function __construct(
    public string $id,
    public float $pph21,
    public string $periode,
    public float $subtotal,
    public float $gaji_bersih,
    public string $created_at,
    public string $updated_at,
    public ?KaryawanDTO $karyawan = null,
    public ?PendapatanKaryawanDTO $pendapatan = null,
    public ?PotonganKaryawanDTO $potongan = null
  ) {}

  /**
   * Convert from Model to DTO
   */
  public static function fromModel(object $gajiKaryawan): self
  {
    $karyawanDTO = $gajiKaryawan->karyawan ? KaryawanDTO::fromModel($gajiKaryawan->karyawan) : null;
    $PendaPendapatanKaryawanDTO = $gajiKaryawan->pendapatan ? PendapatanKaryawanDTO::fromModel($gajiKaryawan->pendapatan) : null;
    $PotonganKaryawanDTO = $gajiKaryawan->potongan ? PotonganKaryawanDTO::fromModel($gajiKaryawan->potongan) : null;

    return new self(
      id: $gajiKaryawan->id,
      pph21: (float) $gajiKaryawan->pph21,
      periode: Carbon::parse($gajiKaryawan->periode)->format('Y-m-d'),
      subtotal: (float) $gajiKaryawan->subtotal,
      gaji_bersih: (float) $gajiKaryawan->gaji_bersih,
      created_at: Carbon::parse($gajiKaryawan->created_at)->toISOString(),
      updated_at: Carbon::parse($gajiKaryawan->updated_at)->toISOString(),
      karyawan: $karyawanDTO,
      pendapatan: $PendaPendapatanKaryawanDTO,
      potongan: $PotonganKaryawanDTO
    );
  }

  /**
   * Convert DTO to array
   */
  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'pph21' => $this->pph21,
      'periode' => $this->periode,
      'subtotal' => $this->subtotal,
      'gaji_bersih' => $this->gaji_bersih,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'karyawan' => $this->karyawan?->toArray(),
      'pendapatan' => $this->pendapatan?->toArray(),
      'potongan' => $this->potongan?->toArray()
    ];
  }
}
