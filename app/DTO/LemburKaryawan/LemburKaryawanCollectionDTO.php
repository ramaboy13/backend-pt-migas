<?php

namespace App\DTO\LemburKaryawan;

use Illuminate\Pagination\LengthAwarePaginator;

class LemburKaryawanCollectionDTO
{
  public function __construct(
    public array $data,
    public array $meta
  ) {}

  /**
   * Convert from Paginator to Collection DTO
   */
  public static function fromPaginator(LengthAwarePaginator $paginator, bool $withKaryawan = true): self
  {
    $lemburDTOs = [];

    foreach ($paginator->items() as $lembur) {
      $dto = LemburKaryawanDTO::fromModel($lembur);

      if (!$withKaryawan) {
        $lemburDTOs[] = $dto->toSimpleArray();
      } else {
        $lemburDTOs[] = $dto->toArray();
      }
    }

    return new self(
      data: $lemburDTOs,
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
