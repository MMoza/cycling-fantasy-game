<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\ValueObjects\ScoringRuleContext;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageModel;

/**
 * Categorías de pronóstico de etapa visibles para una etapa concreta,
 * derivadas del ScoringSystem de la liga.
 */
class StagePredictionCategoryService
{
    /**
     * @return array<int, array{category: string, label: string, points: int}>
     */
    public function forStage(LeagueModel $league, StageModel $stage): array
    {
        $scoringSystem = ScoringSystemModel::with('rules')->find($league->scoring_system_id);

        return $scoringSystem->rules
            ->filter(fn ($rule) => $rule->context === ScoringRuleContext::PreStage)
            ->filter(fn ($rule) => ! str_starts_with($rule->type->value, 'stage_partial_pos_'))
            ->filter(fn ($rule) => $rule->type->value !== 'stage_top_3_partial')
            ->filter(fn ($rule) => $rule->difficulty === null || $rule->difficulty === $stage->difficulty)
            ->map(fn ($rule) => [
                'category' => $this->mapRuleTypeToCategory($rule->type->value),
                'label' => $rule->type->label(),
                'points' => $rule->points,
            ])
            ->values()
            ->toArray();
    }

    private function mapRuleTypeToCategory(string $ruleTypeValue): string
    {
        return match ($ruleTypeValue) {
            'stage_exact_pos_1' => 'stage_position_1',
            'stage_exact_pos_2' => 'stage_position_2',
            'stage_exact_pos_3' => 'stage_position_3',
            'stage_exact_pos_4' => 'stage_position_4',
            'stage_exact_pos_5' => 'stage_position_5',
            'stage_exact_pos_6' => 'stage_position_6',
            'stage_exact_pos_7' => 'stage_position_7',
            'stage_exact_pos_8' => 'stage_position_8',
            'stage_exact_pos_9' => 'stage_position_9',
            'stage_exact_pos_10' => 'stage_position_10',
            default => $ruleTypeValue,
        };
    }
}
