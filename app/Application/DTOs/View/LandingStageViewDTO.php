<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Current stage block of the landing hero.
 */
readonly class LandingStageViewDTO implements Arrayable
{
    public function __construct(
        public int $number,
        public string $name,
        public ?float $distance,
        public string $date,
    ) {}

    public static function fromModel(StageModel $stage): self
    {
        return new self(
            number: $stage->number,
            name: $stage->name,
            distance: $stage->distance,
            date: $stage->date->toDateString(),
        );
    }

    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'name' => $this->name,
            'distance' => $this->distance,
            'date' => $this->date,
        ];
    }
}
