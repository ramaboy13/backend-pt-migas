<?php

namespace App\DTO\PotonganKaryawan;

use Illuminate\Pagination\LengthAwarePaginator;

class PotonganKaryawanCollectionDTO
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
    $potonganDTOs = [];

    foreach ($paginator->items() as $potongan) {
      $potonganDTOs[] = PotonganKaryawanDTO::fromModel($potongan)->toArray();
    }

    return new self(
      data: $potonganDTOs,
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
