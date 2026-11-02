<?php

declare(strict_types=1);

namespace App\Application\DTOs\Finance;

use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementType;

/**
 * Filtros de consulta del listado de movimientos financieros.
 */
readonly class MovementFiltersDTO
{
    public function __construct(
        public int $year,
        public ?MovementType $type,
        public ?MovementCategory $category,
        public ?string $from,
        public ?string $to,
        public int $page,
    ) {}

    /**
     * Copia de los filtros sin el rango de fechas, usada cuando el rango
     * recibido no es válido.
     */
    public function withoutRange(): self
    {
        return new self($this->year, $this->type, $this->category, null, null, $this->page);
    }
}
