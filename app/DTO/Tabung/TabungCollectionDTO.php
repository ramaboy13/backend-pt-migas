<?php

namespace App\DTO\Tabung;

use Illuminate\Pagination\LengthAwarePaginator;

class TabungCollectionDTO
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
    $tabungDTOs = [];

    foreach ($paginator->items() as $tabung) {
      $tabungDTOs[] = TabungDTO::fromModel($tabung)->toArray();
    }

    return new self(
      data: $tabungDTOs,
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
