<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Compact stage used in the dashboard list: {id, number, name, date, type, distance, status}.
 */
readonly class StageSummaryViewDTO implements Arrayable
{
    public function __construct(
        public string $id,
        public int $number,
        public string $name,
        public string $date,
        public string $type,
        public ?string $distance,
        public string $status,
    ) {}

    public static function fromModel(StageModel $stage): self
    {
        return new self(
            id: $stage->id,
            number: $stage->number,
            name: "{$stage->origin} → {$stage->destination}",
            date: $stage->date->format('d M'),
            type: $stage->type->label(),
            distance: $stage->distance ? "{$stage->distance} km" : null,
            status: $stage->status->value,
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
            'distance' => $this->distance,
            'status' => $this->status,
        ];
    }
}
