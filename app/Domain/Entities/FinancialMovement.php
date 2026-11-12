<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Support\Random;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementSource;
use App\Domain\ValueObjects\MovementStatus;
use App\Domain\ValueObjects\MovementType;
use DateTimeImmutable;

/**
 * Movimiento económico del proyecto. Los importes se guardan siempre
 * positivos o cero: el signo contable lo determina el tipo de movimiento
 * (balance = ingresos - gastos).
 */
readonly class FinancialMovement
{
    public function __construct(
        public string $id,
        public MovementType $type,
        public Money $amount,
        public string $concept,
        public MovementCategory $category,
        public DateTimeImmutable $occurredOn,
        public MovementSource $source,
        public ?string $externalId,
        public MovementStatus $status,
        public ?string $notes,
        public ?string $createdBy,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}

    /**
     * Alta manual de un movimiento: origen "manual", estado "confirmed" y sin
     * identificador externo. El usuario creador se asocia al movimiento.
     */
    public static function create(
        MovementType $type,
        Money $amount,
        string $concept,
        MovementCategory $category,
        DateTimeImmutable $occurredOn,
        ?string $notes,
        ?string $createdBy,
    ): self {
        return new self(
            id: Random::uuid(),
            type: $type,
            amount: $amount,
            concept: $concept,
            category: $category,
            occurredOn: $occurredOn,
            source: MovementSource::Manual,
            externalId: null,
            status: MovementStatus::Confirmed,
            notes: $notes,
            createdBy: $createdBy,
        );
    }

    /**
     * Modificación de los campos editables: la identificación, el origen, el
     * estado y el creador del movimiento se mantienen intactos.
     */
    public function update(
        MovementType $type,
        Money $amount,
        string $concept,
        MovementCategory $category,
        DateTimeImmutable $occurredOn,
        ?string $notes,
    ): self {
        return new self(
            id: $this->id,
            type: $type,
            amount: $amount,
            concept: $concept,
            category: $category,
            occurredOn: $occurredOn,
            source: $this->source,
            externalId: $this->externalId,
            status: $this->status,
            notes: $notes,
            createdBy: $this->createdBy,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }
}
