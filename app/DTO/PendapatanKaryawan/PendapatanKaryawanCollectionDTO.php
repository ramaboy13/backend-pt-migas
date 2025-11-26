<?php

namespace App\DTO\PendapatanKaryawan;

use Illuminate\Pagination\LengthAwarePaginator;

class PendapatanKaryawanCollectionDTO
{
  public function __construct(
    public array $data,
    public array $meta
  ) {}

  /**
   * Convert from Paginator to Collection DTO
   */
  public static function fromPaginator(LengthAwarePaginator $paginator, array $lemburDetails = []): self
  {
    $pendapatanDTOs = [];

    foreach ($paginator->items() as $index => $pendapatan) {
      $lemburDetail = $lemburDetails[$index] ?? [];
      $pendapatanDTOs[] = PendapatanKaryawanDTO::fromModel($pendapatan, $lemburDetail)->toArray();
    }

    return new self(
      data: $pendapatanDTOs,
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
