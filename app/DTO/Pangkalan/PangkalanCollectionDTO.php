<?php

namespace App\DTO\Pangkalan;

use Illuminate\Pagination\LengthAwarePaginator;

class PangkalanCollectionDTO
{
  public function __construct(
    public array $data,
    public array $meta
  ) {}

  // Method mengkonvert LengthAwarePaginator ke Collection DTO
  public static function fromPaginator(LengthAwarePaginator $paginator): self
  {
    $pangkalanDTOs = [];

    foreach ($paginator->items() as $pangkalan) {
      $pangkalanDTOs[] = PangkalanDTO::fromModel($pangkalan)->toArray();
    }

    return new self(
      data: $pangkalanDTOs,
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
