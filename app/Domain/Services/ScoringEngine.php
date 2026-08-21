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
        private readonly array $riderTeamMap = [],
    ) {}

    public function calculateStageScore(
        Prediction $prediction,
        StageResult $actualResult,
        int $stageDifficulty,
        ?string $stageId = null,
    ): ScoreEvent {
        $ruleType = $this->getRuleTypeFromCategory($prediction->category);
        $predictedRider = $this->getPredictedRiderId($prediction);
        $predictedTeam = $this->getPredictedTeamId($prediction);

        $isCorrect = $predictedTeam !== null
            ? $this->matchTeamPrediction($prediction, $actualResult, $predictedTeam)
            : $this->matchRiderPrediction($prediction, $actualResult, $predictedRider);

        $rule = $this->findRule($ruleType, $stageDifficulty);

        if (! $isCorrect && $predictedRider !== null) {
            if ($this->isPositionCategory($prediction->category)) {
                $partialRuleType = $this->getPartialRuleTypeForPosition($prediction->category);
                if ($partialRuleType !== null && $this->isRiderInResults($predictedRider, $actualResult)) {
                    $rule = $this->findRule($partialRuleType, $stageDifficulty);
                    $isCorrect = true;
                }
            } elseif ($this->isStageTop3Category($prediction->category) && $this->isRiderInTop3($predictedRider, $actualResult)) {
                $rule = $this->findRule(ScoringRuleType::StageTop3Partial, $stageDifficulty);
                $isCorrect = true;
            }
        }

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
            context: $prediction->category->value,
            stageId: $stageId,
        );
    }

    public function calculatePositionScores(
        array $predictedRiders,
        array $actualResults,
        string $userId,
        string $leagueId,
        ?string $stageId = null,
    ): array {
        $events = [];
        $actualRiderIds = array_column($actualResults, 'rider_id');
        $actualRiderIdSet = array_flip($actualRiderIds);

        foreach ($predictedRiders as $position => $predictedRiderId) {
            if ($predictedRiderId === null || $predictedRiderId === '') {
                continue;
            }

            $dbPosition = $position + 1;
            $actualRiderId = $actualRiderIds[$position] ?? null;
            $isExact = $predictedRiderId === $actualRiderId;

            if ($isExact) {
                $ruleType = ScoringRuleType::from("stage_exact_pos_{$dbPosition}");
                $rule = $this->findRule($ruleType);
            } else {
                $isInTop10 = isset($actualRiderIdSet[$predictedRiderId]);
                if (! $isInTop10) {
                    continue;
                }
                $ruleType = ScoringRuleType::from("stage_partial_pos_{$dbPosition}");
                $rule = $this->findRule($ruleType);
            }

            if (! $rule) {
                continue;
            }

            $events[] = ScoreEvent::create(
                userId: $userId,
                leagueId: $leagueId,
                scoringRuleId: $rule->id,
                points: $rule->points,
                description: sprintf(
                    '%s: %s (Posición %d)',
                    $isExact ? 'Acierto exacto' : 'Acierto parcial',
                    "{$dbPosition}º clasificado",
                    $dbPosition,
                ),
                context: "stage_position_{$dbPosition}",
                stageId: $stageId,
            );
        }

        return $events;
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
            $dbPosition = $position + 1;
            $rule = $this->findRule($ruleType, position: $isExact ? $dbPosition : null);

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
                    $dbPosition,
                ),
                context: "gc_top_5_pos_{$dbPosition}",
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
            $dbPosition = $position + 1;
            $rule = $this->findRule($ruleType, position: $isExact ? $dbPosition : null);

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
                    $dbPosition,
                ),
                context: "{$prediction->category->value}_pos_{$dbPosition}",
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
            PredictionCategory::StagePosition1 => ScoringRuleType::StageExactPos1,
            PredictionCategory::StagePosition2 => ScoringRuleType::StageExactPos2,
            PredictionCategory::StagePosition3 => ScoringRuleType::StageExactPos3,
            PredictionCategory::StagePosition4 => ScoringRuleType::StageExactPos4,
            PredictionCategory::StagePosition5 => ScoringRuleType::StageExactPos5,
            PredictionCategory::StagePosition6 => ScoringRuleType::StageExactPos6,
            PredictionCategory::StagePosition7 => ScoringRuleType::StageExactPos7,
            PredictionCategory::StagePosition8 => ScoringRuleType::StageExactPos8,
            PredictionCategory::StagePosition9 => ScoringRuleType::StageExactPos9,
            PredictionCategory::StagePosition10 => ScoringRuleType::StageExactPos10,
        };
    }

    private function matchRiderPrediction(Prediction $prediction, StageResult $actualResult, ?string $predictedRider): bool
    {
        if ($predictedRider === null) {
            return false;
        }

        return match ($prediction->category) {
            PredictionCategory::StageWinner => $actualResult->position === 1 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StageSecond => $actualResult->position === 2 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StageThird => $actualResult->position === 3 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StageLeader => $predictedRider === $actualResult->riderId && $actualResult->isGcLeader,
            PredictionCategory::StageCombativo => $predictedRider === $actualResult->riderId && $actualResult->isCombativo,
            PredictionCategory::StagePosition1 => $actualResult->position === 1 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StagePosition2 => $actualResult->position === 2 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StagePosition3 => $actualResult->position === 3 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StagePosition4 => $actualResult->position === 4 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StagePosition5 => $actualResult->position === 5 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StagePosition6 => $actualResult->position === 6 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StagePosition7 => $actualResult->position === 7 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StagePosition8 => $actualResult->position === 8 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StagePosition9 => $actualResult->position === 9 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StagePosition10 => $actualResult->position === 10 && $predictedRider === $actualResult->riderId,
        };
    }

    private function matchTeamPrediction(Prediction $prediction, StageResult $actualResult, string $predictedTeam): bool
    {
        $actualTeam = $this->getTeamForRider($actualResult->riderId);

        if ($actualTeam === null) {
            return false;
        }

        return match ($prediction->category) {
            PredictionCategory::StageWinner => $actualResult->position === 1 && $predictedTeam === $actualTeam,
            PredictionCategory::StageSecond => $actualResult->position === 2 && $predictedTeam === $actualTeam,
            PredictionCategory::StageThird => $actualResult->position === 3 && $predictedTeam === $actualTeam,
            default => false,
        };
    }

    private function getPredictedRiderId(Prediction $prediction): ?string
    {
        return $prediction->predictionValue['rider_id'] ?? null;
    }

    private function getPredictedTeamId(Prediction $prediction): ?string
    {
        return $prediction->predictionValue['team_id'] ?? null;
    }

    private function getTeamForRider(string $riderId): ?string
    {
        return $this->riderTeamMap[$riderId] ?? null;
    }

    private function getPredictedRiderAtPosition(Prediction $prediction, int $position): ?string
    {
        $riders = $prediction->predictionValue;

        if (is_array($riders)) {
            $list = $riders['rider_ids'] ?? $riders;
            if (isset($list[$position])) {
                return $list[$position];
            }
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

    private function isPositionCategory(PredictionCategory $category): bool
    {
        return in_array($category, [
            PredictionCategory::StagePosition1,
            PredictionCategory::StagePosition2,
            PredictionCategory::StagePosition3,
            PredictionCategory::StagePosition4,
            PredictionCategory::StagePosition5,
            PredictionCategory::StagePosition6,
            PredictionCategory::StagePosition7,
            PredictionCategory::StagePosition8,
            PredictionCategory::StagePosition9,
            PredictionCategory::StagePosition10,
        ], true);
    }

    private function isStageTop3Category(PredictionCategory $category): bool
    {
        return in_array($category, [
            PredictionCategory::StageWinner,
            PredictionCategory::StageSecond,
            PredictionCategory::StageThird,
        ], true);
    }

    private function isRiderInTop3(?string $riderId, StageResult $actualResult): bool
    {
        return $riderId === $actualResult->riderId && $actualResult->position <= 3;
    }

    private function getPartialRuleTypeForPosition(PredictionCategory $category): ?ScoringRuleType
    {
        return match ($category) {
            PredictionCategory::StagePosition1 => ScoringRuleType::StagePartialPos1,
            PredictionCategory::StagePosition2 => ScoringRuleType::StagePartialPos2,
            PredictionCategory::StagePosition3 => ScoringRuleType::StagePartialPos3,
            PredictionCategory::StagePosition4 => ScoringRuleType::StagePartialPos4,
            PredictionCategory::StagePosition5 => ScoringRuleType::StagePartialPos5,
            PredictionCategory::StagePosition6 => ScoringRuleType::StagePartialPos6,
            PredictionCategory::StagePosition7 => ScoringRuleType::StagePartialPos7,
            PredictionCategory::StagePosition8 => ScoringRuleType::StagePartialPos8,
            PredictionCategory::StagePosition9 => ScoringRuleType::StagePartialPos9,
            PredictionCategory::StagePosition10 => ScoringRuleType::StagePartialPos10,
            default => null,
        };
    }

    private function isRiderInResults(string $riderId, StageResult $actualResult): bool
    {
        return $riderId === $actualResult->riderId && $actualResult->position <= 10;
    }
}
