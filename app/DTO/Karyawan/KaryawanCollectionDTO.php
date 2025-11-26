<?php

namespace App\DTO\Karyawan;

use Illuminate\Pagination\LengthAwarePaginator;

class KaryawanCollectionDTO
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
    $karyawanDTOs = [];

    foreach ($paginator->items() as $karyawan) {
      $karyawanDTOs[] = KaryawanDTO::fromModel($karyawan)->toArray();
    }

    return new self(
      data: $karyawanDTOs,
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
