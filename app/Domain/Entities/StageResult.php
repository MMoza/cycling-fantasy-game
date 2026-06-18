<?php

declare(strict_types=1);

namespace App\Domain\Entities;

readonly class StageResult
{
    public function __construct(
        public string $id,
        public string $stageId,
        public string $riderId,
        public int $position,
        public ?string $time,
        public ?string $gap,
    ) {
    }

    public static function create(
        string $stageId,
        string $riderId,
        int $position,
        ?string $time = null,
        ?string $gap = null,
    ): self {
        return new self(
            id: \Illuminate\Support\Str::uuid()->toString(),
            stageId: $stageId,
            riderId: $riderId,
            position: $position,
            time: $time,
            gap: $gap,
        );
    }

    public function isWinner(): bool
    {
        return $this->position === 1;
    }

    public function isPodium(): bool
    {
        return $this->position <= 3;
    }

    public function isTopFive(): bool
    {
        return $this->position <= 5;
    }
}
