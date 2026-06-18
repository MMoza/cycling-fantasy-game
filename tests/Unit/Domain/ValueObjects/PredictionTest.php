<?php

declare(strict_types=1);

use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\PredictionType;
use App\Domain\ValueObjects\ScoringRuleContext;

test('prediction category returns correct label', function () {
    expect(PredictionCategory::GcTop5->label())->toBe('Top 5 clasificación general');
    expect(PredictionCategory::StageWinner->label())->toBe('Ganador de etapa');
    expect(PredictionCategory::PointsWinner->label())->toBe('Ganador maillot verde');
});

test('prediction category returns correct context', function () {
    expect(PredictionCategory::GcTop5->context())->toBe(ScoringRuleContext::PreRace);
    expect(PredictionCategory::StageWinner->context())->toBe(ScoringRuleContext::PreStage);
});

test('prediction type returns correct label', function () {
    expect(PredictionType::PreRace->label())->toBe('Antes de la carrera');
    expect(PredictionType::PreStage->label())->toBe('Antes de cada etapa');
});
