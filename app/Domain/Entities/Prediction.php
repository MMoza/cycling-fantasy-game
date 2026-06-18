<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\PredictionType;

readonly class Prediction
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $leagueId,
        public PredictionType $type,
        public PredictionCategory $category,
        public ?string $stageId,
        public array $predictionValue,
        public ?string $lockedAt,
    ) {
    }

    public static function create(
        string $userId,
        string $leagueId,
        PredictionType $type,
        PredictionCategory $category,
        array $predictionValue,
        ?string $stageId = null,
    ): self {
        return new self(
            id: \Illuminate\Support\Str::uuid()->toString(),
            userId: $userId,
            leagueId: $leagueId,
            type: $type,
            category: $category,
            stageId: $stageId,
            predictionValue: $predictionValue,
            lockedAt: null,
        );
    }

    public function lock(string $lockedAt): self
    {
        return new self(
            id: $this->id,
            userId: $this->userId,
            leagueId: $this->leagueId,
            type: $this->type,
            category: $this->category,
            stageId: $this->stageId,
            predictionValue: $this->predictionValue,
            lockedAt: $lockedAt,
        );
    }

    public function isLocked(): bool
    {
        return $this->lockedAt !== null;
    }

    public function isRevealed(): bool
    {
        return $this->isLocked();
    }
}
