<?php

namespace App\DTO\TransaksiOperasional;

use Illuminate\Pagination\LengthAwarePaginator;

class TransaksiOperasionalCollectionDTO
{
  public function __construct(
    public array $data,
    public array $meta
  ) {}

  // Method mengkonvert LengthAwarePaginator ke Collection DTO
  public static function fromPaginator(LengthAwarePaginator $paginator): self
  {
    $transaksiDTOs = [];

    foreach ($paginator->items() as $transaksi) {
      $transaksiDTOs[] = TransaksiOperasionalDTO::fromModel($transaksi)->toArray();
    }

    return new self(
      data: $transaksiDTOs,
      meta: [
        'current_page' => $paginator->currentPage(),
        'total' => $paginator->total(),
        'per_page' => $paginator->perPage(),
        'last_page' => $paginator->lastPage()
      ]
    );
  }

  // Method mengkonvert ke array
  public function toArray(): array
  {
    return [
      'data' => $this->data,
      'meta' => $this->meta
    ];
  }
}
