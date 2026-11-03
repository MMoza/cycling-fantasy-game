<?php

declare(strict_types=1);

namespace App\Application\DTOs\Finance;

use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementType;
use DateTimeImmutable;

/**
 * Datos de modificación de un movimiento financiero. La identificación, el
 * origen y el estado del movimiento no son editables.
 */
readonly class UpdateMovementDTO
{
    public function __construct(
        public string $id,
        public MovementType $type,
        public string $amount,
        public string $concept,
        public MovementCategory $category,
        public DateTimeImmutable $occurredOn,
        public ?string $notes,
    ) {}
}
