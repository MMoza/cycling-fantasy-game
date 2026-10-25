<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Next stage of the edition: stage summary plus scheduling and prediction flags.
 */
readonly class NextStageViewDTO implements Arrayable
{
    public function __construct(
        public string $id,
        public int $number,
        public string $name,
        public string $date,
        public string $type,
        public string $typeValue,
        public ?string $distance,
        public ?float $distanceValue,
        public string $origin,
        public string $destination,
        public string $status,
        public ?string $scheduledStart,
        public ?int $difficulty,
        public bool $hasPredictions,
    ) {}

    public static function fromModel(StageModel $stage, bool $hasPredictions): self
    {
        return new self(
            id: $stage->id,
            number: $stage->number,
            name: $stage->name,
            date: $stage->date->format('d M'),
            type: $stage->type->label(),
            typeValue: $stage->type->value,
            distance: $stage->distance ? "{$stage->distance} km" : null,
            distanceValue: $stage->distance,
            origin: $stage->origin,
            destination: $stage->destination,
            status: $stage->status->value,
            scheduledStart: $stage->scheduled_start?->toIso8601String(),
            difficulty: $stage->difficulty,
            hasPredictions: $hasPredictions,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'name' => $this->name,
            'date' => $this->date,
            'type' => $this->type,
            'type_value' => $this->typeValue,
            'distance' => $this->distance,
            'distance_value' => $this->distanceValue,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'status' => $this->status,
            'scheduled_start' => $this->scheduledStart,
            'difficulty' => $this->difficulty,
            'has_predictions' => $this->hasPredictions,
        ];
    }
}
