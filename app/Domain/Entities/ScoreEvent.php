<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use Illuminate\Support\Str;

readonly class ScoreEvent
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $leagueId,
        public string $scoringRuleId,
        public int $points,
        public string $description,
        public string $context,
        public ?string $stageId = null,
    ) {}

    public static function create(
        string $userId,
        string $leagueId,
        string $scoringRuleId,
        int $points,
        string $description,
        string $context,
        ?string $stageId = null,
    ): self {
        return new self(
            id: Str::uuid()->toString(),
            userId: $userId,
            leagueId: $leagueId,
            scoringRuleId: $scoringRuleId,
            points: $points,
            description: $description,
            context: $context,
            stageId: $stageId,
        );
    }

    public function isPositive(): bool
    {
        return $this->points > 0;
    }

    public function isNegative(): bool
    {
        return $this->points < 0;
    }

    public function isZero(): bool
    {
        return $this->points === 0;
    }
}
