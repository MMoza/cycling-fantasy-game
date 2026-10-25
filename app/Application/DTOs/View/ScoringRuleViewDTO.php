<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Infrastructure\Persistence\Models\ScoringRuleModel;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Scoring rule: {type, label, context, points, difficulty, position}.
 */
readonly class ScoringRuleViewDTO implements Arrayable
{
    public function __construct(
        public string $type,
        public string $label,
        public string $context,
        public int $points,
        public ?int $difficulty,
        public ?int $position,
    ) {}

    public static function fromModel(ScoringRuleModel $rule): self
    {
        return new self(
            type: $rule->type->value,
            label: $rule->type->label(),
            context: $rule->type->context()->value,
            points: $rule->points,
            difficulty: $rule->difficulty,
            position: $rule->position,
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'label' => $this->label,
            'context' => $this->context,
            'points' => $this->points,
            'difficulty' => $this->difficulty,
            'position' => $this->position,
        ];
    }
}
