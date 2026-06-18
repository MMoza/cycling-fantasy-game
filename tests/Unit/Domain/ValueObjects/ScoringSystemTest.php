<?php

declare(strict_types=1);

use App\Domain\ValueObjects\ScoringRuleContext;
use App\Domain\ValueObjects\ScoringRuleType;
use App\Domain\ValueObjects\ScoringSystemType;

test('scoring system type returns correct label', function () {
    expect(ScoringSystemType::Standard->label())->toBe('Estándar');
    expect(ScoringSystemType::Aggressive->label())->toBe('Agresivo');
    expect(ScoringSystemType::Conservative->label())->toBe('Conservador');
    expect(ScoringSystemType::Custom->label())->toBe('Personalizado');
});

test('scoring system type returns correct description', function () {
    expect(ScoringSystemType::Standard->description())->toBe('Puntuación equilibrada');
    expect(ScoringSystemType::Aggressive->description())->toBe('Premia más al ganador, menos al resto');
    expect(ScoringSystemType::Conservative->description())->toBe('Puntuación más repartida');
    expect(ScoringSystemType::Custom->description())->toBe('Reglas personalizadas');
});

test('scoring rule type returns correct label', function () {
    expect(ScoringRuleType::StageWinner->label())->toBe('Ganador de etapa');
    expect(ScoringRuleType::GcTop5->label())->toBe('Top 5 clasificación general');
    expect(ScoringRuleType::PointsWinner->label())->toBe('Ganador maillot verde');
});

test('scoring rule type returns correct context', function () {
    expect(ScoringRuleType::StageWinner->context())->toBe(ScoringRuleContext::PreStage);
    expect(ScoringRuleType::GcTop5->context())->toBe(ScoringRuleContext::PreRace);
});

test('scoring rule context returns correct label', function () {
    expect(ScoringRuleContext::PreRace->label())->toBe('Antes de la carrera');
    expect(ScoringRuleContext::PreStage->label())->toBe('Antes de cada etapa');
});
