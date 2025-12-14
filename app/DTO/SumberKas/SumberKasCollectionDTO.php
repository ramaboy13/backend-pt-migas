<?php

namespace App\DTO\SumberKas;

class SumberKasCollectionDTO
{
    public function __construct(
        public readonly array $items,
        public readonly array $meta
    ) {}

    public static function fromPaginator($paginator): self
    {
        $items = collect($paginator->items())->map(function ($item) {
            return SumberKasDTO::fromModel($item);
        })->toArray();

        return new self(
            items: $items,
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ]
        );
    }

    public function toArray(): array
    {
        return [
            'data' => array_map(fn ($item) => $item->toArray(), $this->items),
            'meta' => $this->meta,
        ];
    }
}
