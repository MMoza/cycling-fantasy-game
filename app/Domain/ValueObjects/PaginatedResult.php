<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

/**
 * Resultado paginado independiente del framework: los repositorios devuelven
 * esta estructura y Application nunca ve las clases de paginación de Laravel.
 *
 * @template T
 */
final readonly class PaginatedResult
{
    /**
     * @param  list<T>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
    ) {}

    public function lastPage(): int
    {
        if ($this->perPage <= 0) {
            return 1;
        }

        return max(1, intdiv($this->total + $this->perPage - 1, $this->perPage));
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }
}
