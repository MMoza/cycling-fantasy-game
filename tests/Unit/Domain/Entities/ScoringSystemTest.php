<?php

declare(strict_types=1);

use App\Domain\Entities\ScoringRule;
use App\Domain\Entities\ScoringSystem;
use App\Domain\ValueObjects\ScoringRuleContext;
use App\Domain\ValueObjects\ScoringRuleType;
use App\Domain\ValueObjects\ScoringSystemType;

test('creates scoring system with factory method', function () {
    $system = ScoringSystem::create(
        name: 'Estándar',
        type: ScoringSystemType::Standard,
        description: 'Puntuación equilibrada',
    );

    expect($system->id)->toBeUuid();
    expect($system->name)->toBe('Estándar');
    expect($system->type)->toBe(ScoringSystemType::Standard);
    expect($system->description)->toBe('Puntuación equilibrada');
    expect($system->rules)->toHaveCount(0);
});

test('adds rule to scoring system', function () {
    $system = ScoringSystem::create(
        name: 'Estándar',
        type: ScoringSystemType::Standard,
        description: 'Puntuación equilibrada',
    );

    $rule = ScoringRule::create(
        scoringSystemId: $system->id,
        type: ScoringRuleType::StageWinner,
        points: 50,
    );

    $systemWithRule = $system->addRule($rule);

    expect($systemWithRule->rules)->toHaveCount(1);
    expect($systemWithRule->getPointsForRule(ScoringRuleType::StageWinner))->toBe(50);
});

test('returns zero points for non-existent rule', function () {
    $system = ScoringSystem::create(
        name: 'Estándar',
        type: ScoringSystemType::Standard,
        description: 'Puntuación equilibrada',
    );

    expect($system->getPointsForRule(ScoringRuleType::StageWinner))->toBe(0);
});

test('filters rules by context', function () {
    $system = ScoringSystem::create(
        name: 'Estándar',
        type: ScoringSystemType::Standard,
        description: 'Puntuación equilibrada',
    );

    $stageRule = ScoringRule::create($system->id, ScoringRuleType::StageWinner, 50);
    $raceRule = ScoringRule::create($system->id, ScoringRuleType::GcTop5, 100);

    $system = $system->addRule($stageRule)->addRule($raceRule);

    $preStageRules = $system->getRulesForContext(ScoringRuleContext::PreStage);
    $preRaceRules = $system->getRulesForContext(ScoringRuleContext::PreRace);

    expect($preStageRules)->toHaveCount(1);
    expect($preRaceRules)->toHaveCount(1);
});
