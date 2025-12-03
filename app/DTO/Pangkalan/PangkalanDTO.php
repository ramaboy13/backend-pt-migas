<?php

namespace App\DTO\Pangkalan;

use Carbon\Carbon;

class PangkalanDTO
{
  public function __construct(
    public string $id,
    public int $regist_id,
    public string $nama,
    public int $no_ktp,
    public ?string $alamat,
    public float $harga_satuan,
    public string $created_at,
    public string $updated_at
  ) {}

  /**
   * Convert from Model to DTO
   */
  public static function fromModel(object $pangkalan): self
  {
    return new self(
      id: $pangkalan->id,
      regist_id: (int) $pangkalan->regist_id,
      nama: $pangkalan->nama,
      no_ktp: (int) $pangkalan->no_ktp,
      alamat: $pangkalan->alamat,
      harga_satuan: (float) $pangkalan->harga_satuan,
      created_at: Carbon::parse($pangkalan->created_at)->toISOString(),
      updated_at: Carbon::parse($pangkalan->updated_at)->toISOString()
    );
  }

  /**
   * Convert DTO to array
   */
  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'regist_id' => $this->regist_id,
      'nama' => $this->nama,
      'no_ktp' => $this->no_ktp,
      'alamat' => $this->alamat,
      'harga_satuan' => $this->harga_satuan,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at
    ];
  }
}
