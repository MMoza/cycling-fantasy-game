<?php

declare(strict_types=1);

namespace App\Application\DTOs\Finance;

use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementSource;
use App\Domain\ValueObjects\MovementStatus;
use App\Domain\ValueObjects\MovementType;
use App\Infrastructure\Persistence\Models\FinancialMovementModel;

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

    public static function fromModel(FinancialMovementModel $movement): self
    {
        /** @var MovementType $type */
        $type = $movement->type;
        /** @var MovementCategory $category */
        $category = $movement->category;
        /** @var MovementSource $source */
        $source = $movement->source;
        /** @var MovementStatus $status */
        $status = $movement->status;

        return new self(
            id: $movement->id,
            type: $type->value,
            typeLabel: $type->label(),
            amount: (string) $movement->amount,
            concept: $movement->concept,
            category: $category->value,
            categoryLabel: $category->label($type),
            occurredOn: $movement->occurred_on->format('Y-m-d'),
            source: $source->value,
            sourceLabel: $source->label(),
            status: $status->value,
            statusLabel: $status->label(),
            notes: $movement->notes,
            createdBy: $movement->creator?->name,
            createdAt: $movement->created_at->format('d/m/Y H:i'),
            updatedAt: $movement->updated_at->format('d/m/Y H:i'),
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
