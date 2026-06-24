<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Entities\Prediction;
use App\Domain\Entities\ScoreEvent;
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
        ?string $stageId = null,
    ): ScoreEvent {
        $ruleType = $this->getRuleTypeFromCategory($prediction->category);
        $points = $this->scoringSystem->getPointsForRule($ruleType);
        $isCorrect = $this->isPredictionCorrect($prediction, $actualResult);

        $finalPoints = $isCorrect ? $points : 0;
        $description = $this->buildDescription($prediction->category, $actualResult, $isCorrect);

        return ScoreEvent::create(
            userId: $prediction->userId,
            leagueId: $prediction->leagueId,
            scoringRuleId: $this->getRuleId($ruleType),
            points: $finalPoints,
            description: $description,
            context: "stage_{$actualResult->position}",
            stageId: $stageId,
        );
    }

    public function calculateGcScore(
        Prediction $prediction,
        string $actualRiderId,
        int $actualPosition,
    ): ScoreEvent {
        $ruleType = $this->getRuleTypeFromCategory($prediction->category);
        $points = $this->scoringSystem->getPointsForRule($ruleType);
        $isCorrect = $prediction->predictionValue[$actualPosition] === $actualRiderId;

        $finalPoints = $isCorrect ? $points : 0;
        $description = $this->buildGcDescription($prediction->category, $actualPosition, $isCorrect);

        return ScoreEvent::create(
            userId: $prediction->userId,
            leagueId: $prediction->leagueId,
            scoringRuleId: $this->getRuleId($ruleType),
            points: $finalPoints,
            description: $description,
            context: 'gc_final',
        );
    }

    public function calculateTotalScore(Collection $scoreEvents): int
    {
        return $scoreEvents->sum(fn (ScoreEvent $event) => $event->points);
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

    private function isPredictionCorrect(Prediction $prediction, StageResult $actualResult): bool
    {
        $predictedRider = $prediction->predictionValue['rider_id'] ?? null;

        return match ($prediction->category) {
            PredictionCategory::StageWinner => $actualResult->position === 1 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StageSecond => $actualResult->position === 2 && $predictedRider === $actualResult->riderId,
            PredictionCategory::StageThird => $actualResult->position === 3 && $predictedRider === $actualResult->riderId,
            default => $predictedRider === $actualResult->riderId,
        };
    }

    private function buildDescription(
        PredictionCategory $category,
        StageResult $result,
        bool $isCorrect,
    ): string {
        $label = $category->label();
        $status = $isCorrect ? 'Acierto' : 'Fallo';

        return "{$status}: {$label} (Posición: {$result->position})";
    }

    private function buildGcDescription(
        PredictionCategory $category,
        int $position,
        bool $isCorrect,
    ): string {
        $label = $category->label();
        $status = $isCorrect ? 'Acierto' : 'Fallo';

        return "{$status}: {$label} (Posición: {$position})";
    }

    private function getRuleId(ScoringRuleType $type): string
    {
        $rule = $this->scoringSystem->rules->first(fn ($rule) => $rule->type === $type);

        return $rule?->id ?? '';
    }
}
