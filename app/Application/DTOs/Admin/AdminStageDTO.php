<?php

declare(strict_types=1);

namespace App\Application\DTOs\Admin;

readonly class AdminStageDTO
{
    public function __construct(
        public string $id,
        public int $number,
        public string $name,
        public string $date,
        public string $type,
        public string $typeValue,
        public ?float $distance,
        public ?int $elevationGain,
        public ?int $difficulty,
        public ?string $origin,
        public ?string $destination,
        public string $status,
    ) {}
}

readonly class CreateStageDTO
{
    public function __construct(
        public int $number,
        public string $name,
        public string $date,
        public string $type,
        public ?float $distance,
        public ?int $elevationGain,
        public ?int $difficulty,
        public string $origin,
        public string $destination,
        public ?string $scheduledStart,
        public ?array $profileImage = null,
    ) {}
}

readonly class StageResultDTO
{
    public function __construct(
        public string $riderId,
        public int $position,
        public ?string $time,
        public ?string $gap,
        public bool $isGcLeader,
        public bool $isCombativo,
    ) {}
}
