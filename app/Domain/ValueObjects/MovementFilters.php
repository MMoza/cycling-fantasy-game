<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use DateTimeImmutable;

/**
 * Criterios de consulta del listado de movimientos financieros. El año
 * natural (la temporada) siempre acota la búsqueda; el rango de fechas es
 * opcional y se aplica dentro del año.
 */
readonly class MovementFilters
{
    public function __construct(
        public int $year,
        public ?MovementType $type,
        public ?MovementCategory $category,
        public ?DateTimeImmutable $from,
        public ?DateTimeImmutable $to,
        public int $page = 1,
    ) {}

    /**
     * Copia de los filtros sin el rango de fechas, usada cuando el rango
     * recibido no es válido.
     */
    public function withoutRange(): self
    {
        return new self($this->year, $this->type, $this->category, null, null, $this->page);
    }

    public function periodStart(): string
    {
        return sprintf('%04d-01-01', $this->year);
    }

    public function periodEnd(): string
    {
        return sprintf('%04d-12-31', $this->year);
    }
}
