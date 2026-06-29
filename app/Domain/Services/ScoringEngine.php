<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Entities\Prediction;
use App\Domain\Entities\ScoreEvent;
use App\Domain\Entities\ScoringRule;
use App\Domain\Entities\ScoringSystem;
use App\Domain\Entities\StageResult;
use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\ScoringRuleType;
use Illuminate\Support\Collection;

class ScoringEngine
{
    public function __construct(
        private readonly ScoringSystem $scoringSystem,
    ) {}

    public function calculateStageScore(
        Prediction $prediction,
        StageResult $actualResult,
        int $stageDifficulty,
        ?string $stageId = null,
    ): ScoreEvent {
        $ruleType = $this->getRuleTypeFromCategory($prediction->category);
        $predictedRider = $this->getPredictedRiderId($prediction);

        $isCorrect = match ($prediction->category) {
            PredictionCategory::StageWinner => $actualResult->position === 1 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StageSecond => $actualResult->position === 2 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StageThird => $actualResult->position === 3 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StageLeader => $predictedRider === $actualResult->riderId && $actualResult->isGcLeader,
            PredictionCategory::StageCombativo => $predictedRider === $actualResult->riderId && $actualResult->isCombativo,
            default => $predictedRider === $actualResult->riderId,
        };

        $rule = $this->findRule($ruleType, $stageDifficulty);
        $finalPoints = $isCorrect && $rule ? $rule->points : 0;

        $description = sprintf(
            '%s: %s (%s)',
            $isCorrect ? 'Acierto' : 'Fallo',
            $prediction->category->label(),
            $actualResult->isWinner() ? 'Ganador' : "Posición {$actualResult->position}",
        );

        return ScoreEvent::create(
            userId: $prediction->userId,
            leagueId: $prediction->leagueId,
            scoringRuleId: $rule?->id ?? '',
            points: $finalPoints,
            description: $description,
            context: "stage_{$actualResult->position}",
            stageId: $stageId,
        );
    }

    public function calculateGcTop5Score(
        Prediction $prediction,
        array $actualTop5,
    ): array {
        $events = [];

        foreach ($actualTop5 as $position => $actualRiderId) {
            $predictedRiderId = $this->getPredictedRiderAtPosition($prediction, $position);
            $isExact = $predictedRiderId === $actualRiderId;

            $ruleType = $isExact ? ScoringRuleType::GcTop5 : ScoringRuleType::GcTop5Partial;
            $rule = $this->findRule($ruleType, position: $isExact ? $position : null);

            if (! $rule) {
                continue;
            }

            $isCorrect = $isExact || $this->isRiderInTop5($predictedRiderId, $actualTop5);

            if (! $isCorrect) {
                continue;
            }

            $events[] = ScoreEvent::create(
                userId: $prediction->userId,
                leagueId: $prediction->leagueId,
                scoringRuleId: $rule->id,
                points: $rule->points,
                description: sprintf(
                    '%s: Top 5 General (Posición %d)',
                    $isExact ? 'Acierto exacto' : 'Acierto parcial',
                    $position,
                ),
                context: "gc_top_5_pos_{$position}",
            );
        }

        return $events;
    }

    public function calculateJerseyScore(
        Prediction $prediction,
        array $actualPodium,
        ScoringRuleType $exactType,
        ScoringRuleType $partialType,
    ): array {
        $events = [];

        foreach ($actualPodium as $position => $actualRiderId) {
            $predictedRiderId = $this->getPredictedRiderAtPosition($prediction, $position);
            $isExact = $predictedRiderId === $actualRiderId;

            $ruleType = $isExact ? $exactType : $partialType;
            $rule = $this->findRule($ruleType, position: $isExact ? $position : null);

            if (! $rule) {
                continue;
            }

            $isCorrect = $isExact || $this->isRiderInArray($predictedRiderId, $actualPodium);

            if (! $isCorrect) {
                continue;
            }

            $events[] = ScoreEvent::create(
                userId: $prediction->userId,
                leagueId: $prediction->leagueId,
                scoringRuleId: $rule->id,
                points: $rule->points,
                description: sprintf(
                    '%s: %s (Posición %d)',
                    $isExact ? 'Acierto exacto' : 'Acierto parcial',
                    $prediction->category->label(),
                    $position,
                ),
                context: "{$prediction->category->value}_pos_{$position}",
            );
        }

        return $events;
    }

    public function calculateSimpleScore(
        Prediction $prediction,
        string $actualRiderId,
        ScoringRuleType $ruleType,
    ): ScoreEvent {
        $predictedRiderId = $this->getPredictedRiderId($prediction);
        $isCorrect = $predictedRiderId === $actualRiderId;
        $rule = $this->findRule($ruleType);

        return ScoreEvent::create(
            userId: $prediction->userId,
            leagueId: $prediction->leagueId,
            scoringRuleId: $rule?->id ?? '',
            points: $isCorrect && $rule ? $rule->points : 0,
            description: sprintf(
                '%s: %s',
                $isCorrect ? 'Acierto' : 'Fallo',
                $prediction->category->label(),
            ),
            context: $prediction->category->value,
        );
    }

    public function calculateTotalScore(Collection $scoreEvents): int
    {
        return $scoreEvents->sum(fn (ScoreEvent $event) => $event->points);
    }

    private function findRule(ScoringRuleType $type, ?int $difficulty = null, ?int $position = null): ?ScoringRule
    {
        return $this->scoringSystem->rules->first(
            fn (ScoringRule $rule) => $rule->type === $type
                && ($difficulty === null || $rule->difficulty === $difficulty || $rule->difficulty === null)
                && ($position === null || $rule->position === $position)
        );
    }

    private function getRuleTypeFromCategory(PredictionCategory $category): ScoringRuleType
    {
        return match ($category) {
            PredictionCategory::GcTop5 => ScoringRuleType::GcTop5,
            PredictionCategory::PointsWinner => ScoringRuleType::PointsWinner,
            PredictionCategory::MountainsWinner => ScoringRuleType::MountainsWinner,
            PredictionCategory::YouthWinner => ScoringRuleType::YouthWinner,
            PredictionCategory::TeamsWinner => ScoringRuleType::TeamsWinner,
            PredictionCategory::SuperCombativo => ScoringRuleType::SuperCombativo,
            PredictionCategory::StageWinner => ScoringRuleType::StageWinner,
            PredictionCategory::StageSecond => ScoringRuleType::StageSecond,
            PredictionCategory::StageThird => ScoringRuleType::StageThird,
            PredictionCategory::StageLeader => ScoringRuleType::StageLeader,
            PredictionCategory::StageCombativo => ScoringRuleType::StageCombativo,
        };
    }

    private function getPredictedRiderId(Prediction $prediction): ?string
    {
        return $prediction->predictionValue['rider_id'] ?? null;
    }

    private function getPredictedRiderAtPosition(Prediction $prediction, int $position): ?string
    {
        $riders = $prediction->predictionValue;

        if (is_array($riders) && isset($riders[$position])) {
            return $riders[$position];
        }

        if ($position === 0 && is_array($riders) && isset($riders['rider_id'])) {
            return $riders['rider_id'];
        }

        return null;
    }

    private function isRiderInTop5(?string $riderId, array $actualTop5): bool
    {
        if (! $riderId) {
            return false;
        }

        return in_array($riderId, $actualTop5, true);
    }

    private function isRiderInArray(?string $riderId, array $riders): bool
    {
        if (! $riderId) {
            return false;
        }

        return in_array($riderId, $riders, true);
    }
}
