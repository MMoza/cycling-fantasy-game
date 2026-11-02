<?php

declare(strict_types=1);

namespace App\Application\DTOs\Finance;

use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementType;
use Carbon\CarbonImmutable;

/**
 * Datos de alta manual de un movimiento financiero. El origen y el estado
 * los establece el caso de uso, nunca el usuario.
 */
readonly class StoreMovementDTO
{
    public function __construct(
        public MovementType $type,
        public string $amount,
        public string $concept,
        public MovementCategory $category,
        public CarbonImmutable $occurredOn,
        public ?string $notes,
        public string $createdBy,
    ) {}
}
