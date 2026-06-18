<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\ScoringRuleContext;
use App\Domain\ValueObjects\ScoringRuleType;
use App\Domain\ValueObjects\ScoringSystemType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

readonly class ScoringSystem
{
    /**
     * @param  Collection<ScoringRule>  $rules
     */
    public function __construct(
        public string $id,
        public string $name,
        public ScoringSystemType $type,
        public string $description,
        public Collection $rules = new Collection,
    ) {}

    public static function create(
        string $name,
        ScoringSystemType $type,
        string $description,
    ): self {
        return new self(
            id: Str::uuid()->toString(),
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
            rules: $this->rules->push($rule),
        );
    }

    public function getPointsForRule(ScoringRuleType $type): int
    {
        $rule = $this->rules->first(fn (ScoringRule $rule) => $rule->type === $type);

        return $rule?->points ?? 0;
    }

    public function getRulesForContext(ScoringRuleContext $context): Collection
    {
        return $this->rules->filter(fn (ScoringRule $rule) => $rule->context === $context);
    }
}
