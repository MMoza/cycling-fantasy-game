<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\ScoringRuleContext;
use App\Domain\ValueObjects\ScoringRuleType;
use Illuminate\Support\Str;

readonly class ScoringRule
{
    public function __construct(
        public string $id,
        public string $scoringSystemId,
        public ScoringRuleType $type,
        public ScoringRuleContext $context,
        public int $points,
    ) {}

    public static function create(
        string $scoringSystemId,
        ScoringRuleType $type,
        int $points,
    ): self {
        return new self(
            id: Str::uuid()->toString(),
            scoringSystemId: $scoringSystemId,
            type: $type,
            context: $type->context(),
            points: $points,
        );
    }
}
