<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Support\Random;
use App\Domain\ValueObjects\ScoringRuleContext;
use App\Domain\ValueObjects\ScoringRuleType;
use App\Domain\ValueObjects\ScoringSystemType;

readonly class ScoringSystem
{
    /**
     * @param  list<ScoringRule>  $rules
     */
    public function __construct(
        public string $id,
        public string $name,
        public ScoringSystemType $type,
        public string $description,
        public array $rules = [],
    ) {}

    public static function create(
        string $name,
        ScoringSystemType $type,
        string $description,
    ): self {
        return new self(
            id: Random::uuid(),
            name: $name,
            type: $type,
            description: $description,
        );
    }

    public function addRule(ScoringRule $rule): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            type: $this->type,
            description: $this->description,
            rules: [...$this->rules, $rule],
        );
    }

    public function getPointsForRule(ScoringRuleType $type): int
    {
        foreach ($this->rules as $rule) {
            if ($rule->type === $type) {
                return $rule->points;
            }
        }

        return 0;
    }

    /**
     * @return list<ScoringRule>
     */
    public function getRulesForContext(ScoringRuleContext $context): array
    {
        return array_values(array_filter(
            $this->rules,
            fn (ScoringRule $rule): bool => $rule->context === $context,
        ));
    }
}
