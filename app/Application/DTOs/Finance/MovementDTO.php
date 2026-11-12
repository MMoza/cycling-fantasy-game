<?php

declare(strict_types=1);

namespace App\Application\DTOs\Finance;

use App\Domain\Entities\FinancialMovement;

/**
 * Fila de presentación de un movimiento financiero (contrato snake_case).
 */
readonly class MovementDTO
{
    public function __construct(
        public string $id,
        public string $type,
        public string $typeLabel,
        public string $amount,
        public string $concept,
        public string $category,
        public string $categoryLabel,
        public string $occurredOn,
        public string $source,
        public string $sourceLabel,
        public string $status,
        public string $statusLabel,
        public ?string $notes,
        public ?string $createdBy,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromEntity(FinancialMovement $movement, ?string $createdByName = null): self
    {
        return new self(
            id: $movement->id,
            type: $movement->type->value,
            typeLabel: $movement->type->label(),
            amount: $movement->amount->toDecimalString(),
            concept: $movement->concept,
            category: $movement->category->value,
            categoryLabel: $movement->category->label($movement->type),
            occurredOn: $movement->occurredOn->format('Y-m-d'),
            source: $movement->source->value,
            sourceLabel: $movement->source->label(),
            status: $movement->status->value,
            statusLabel: $movement->status->label(),
            notes: $movement->notes,
            createdBy: $createdByName,
            createdAt: $movement->createdAt?->format('d/m/Y H:i') ?? '',
            updatedAt: $movement->updatedAt?->format('d/m/Y H:i') ?? '',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_label' => $this->typeLabel,
            'amount' => $this->amount,
            'concept' => $this->concept,
            'category' => $this->category,
            'category_label' => $this->categoryLabel,
            'occurred_on' => $this->occurredOn,
            'source' => $this->source,
            'source_label' => $this->sourceLabel,
            'status' => $this->status,
            'status_label' => $this->statusLabel,
            'notes' => $this->notes,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
