<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

/**
 * Total confirmado de una categoría de movimiento en una temporada.
 */
readonly class FinanceCategoryTotal
{
    public function __construct(
        public MovementType $type,
        public MovementCategory $category,
        public Money $total,
        public int $count,
    ) {}
}
