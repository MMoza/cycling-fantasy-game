<?php

declare(strict_types=1);

namespace App\Application\DTOs;

readonly class StageDetailDTO
{
    public function __construct(
        public string $id,
        public int $number,
        public string $name,
        public string $date,
        public string $type,
        public string $typeValue,
        public ?string $distance,
        public ?int $elevationGain,
        public ?string $profileImage,
        public ?string $origin,
        public ?string $destination,
        public ?int $difficulty,
        public string $status,
        public ?string $scheduledStart,
        public ?string $liveStreamUrl,
    ) {}
}
