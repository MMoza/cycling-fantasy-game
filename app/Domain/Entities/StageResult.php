<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use Illuminate\Support\Str;

readonly class StageResult
{
    public function __construct(
        public string $id,
        public string $stageId,
        public string $riderId,
        public int $position,
        public ?string $time,
        public ?string $gap,
        public bool $isGcLeader = false,
        public bool $isCombativo = false,
    ) {}

    public static function create(
        string $stageId,
        string $riderId,
        int $position,
        ?string $time = null,
        ?string $gap = null,
        bool $isGcLeader = false,
        bool $isCombativo = false,
    ): self {
        return new self(
            id: Str::uuid()->toString(),
            stageId: $stageId,
            riderId: $riderId,
            position: $position,
            time: $time,
            gap: $gap,
            isGcLeader: $isGcLeader,
            isCombativo: $isCombativo,
        );
    }

    public static function fromRow(\stdClass $row): self
    {
        return new self(
            id: $row->id,
            stageId: $row->stage_id,
            riderId: $row->rider_id,
            position: (int) $row->position,
            time: $row->time ?? null,
            gap: $row->gap ?? null,
            isGcLeader: (bool) ($row->is_gc_leader ?? false),
            isCombativo: (bool) ($row->is_combativo ?? false),
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
