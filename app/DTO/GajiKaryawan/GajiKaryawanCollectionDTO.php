<?php

namespace App\DTO\GajiKaryawan;

use Illuminate\Pagination\LengthAwarePaginator;

class GajiKaryawanCollectionDTO
{
  public function __construct(
    public array $data,
    public array $meta
  ) {}

  /**
   * Convert from Paginator to Collection DTO
   */
  public static function fromPaginator(LengthAwarePaginator $paginator): self
  {
    $gajiKaryawanDTOs = [];

    foreach ($paginator->items() as $gajiKaryawan) {
      $gajiKaryawanDTOs[] = GajiKaryawanDTO::fromModel($gajiKaryawan)->toArray();
    }

    return new self(
      data: $gajiKaryawanDTOs,
      meta: [
        'current_page' => $paginator->currentPage(),
        'total' => $paginator->total(),
        'per_page' => $paginator->perPage(),
        'last_page' => $paginator->lastPage()
      ]
    );
  }

  /**
   * Convert to array
   */
  public function toArray(): array
  {
    return [
      'data' => $this->data,
      'meta' => $this->meta
    ];
  }
}
