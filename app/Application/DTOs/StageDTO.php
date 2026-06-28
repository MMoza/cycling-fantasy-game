<?php

declare(strict_types=1);

namespace App\Application\DTOs;

readonly class StageDTO
{
    public function __construct(
        public string $id,
        public int $number,
        public string $name,
        public string $date,
        public string $type,
        public string $typeValue,
        public ?float $distance,
        public ?string $origin,
        public ?string $destination,
        public string $status,
        public ?int $difficulty,
        public ?string $profileImage,
        public ?int $elevationGain,
        public ?string $scheduledStart,
        public bool $hasPredictions = false,
        public int $points = 0,
    ) {}
}
