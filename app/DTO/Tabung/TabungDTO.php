<?php

namespace App\DTO\Tabung;

use Carbon\Carbon;

class TabungDTO
{
  public function __construct(
    public string $id,
    public string $name,
    public float $berat,
    public string $created_at,
    public string $updated_at
  ) {}

  /**
   * Convert from Model to DTO
   */
  public static function fromModel(object $tabung): self
  {
    return new self(
      id: $tabung->id,
      name: $tabung->name,
      berat: (float) $tabung->berat,
      created_at: Carbon::parse($tabung->created_at)->toISOString(),
      updated_at: Carbon::parse($tabung->updated_at)->toISOString()
    );
  }

  /**
   * Convert DTO to array
   */
  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'berat' => $this->berat,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at
    ];
  }
}
