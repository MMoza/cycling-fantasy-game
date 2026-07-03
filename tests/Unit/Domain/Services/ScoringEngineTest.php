<?php

declare(strict_types=1);

use App\Domain\Entities\Prediction;
use App\Domain\Entities\ScoreEvent;
use App\Domain\Entities\ScoringRule;
use App\Domain\Entities\ScoringSystem;
use App\Domain\Entities\StageResult;
use App\Domain\Services\ScoringEngine;
use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\PredictionType;
use App\Domain\ValueObjects\ScoringRuleType;
use App\Domain\ValueObjects\ScoringSystemType;

test('calculates correct stage winner prediction', function () {
    $system = ScoringSystem::create('Test', ScoringSystemType::Standard, 'Test');
    $rule = ScoringRule::create($system->id, ScoringRuleType::StageWinner, 50, difficulty: 1);
    $system = $system->addRule($rule);

    $engine = new ScoringEngine($system);

    $prediction = Prediction::create(
        userId: 'user-uuid',
        leagueId: 'league-uuid',
        type: PredictionType::PreStage,
        category: PredictionCategory::StageWinner,
        predictionValue: ['rider_id' => 'rider-1'],
        stageId: 'stage-uuid',
    );

    $result = StageResult::create('stage-uuid', 'rider-1', 1, '4:30:00');

    $event = $engine->calculateStageScore($prediction, $result, stageDifficulty: 1);

    expect($event->points)->toBe(50);
    expect($event->isPositive())->toBeTrue();
    expect($event->description)->toContain('Acierto');
});

test('calculates incorrect stage winner prediction', function () {
    $system = ScoringSystem::create('Test', ScoringSystemType::Standard, 'Test');
    $rule = ScoringRule::create($system->id, ScoringRuleType::StageWinner, 50, difficulty: 1);
    $system = $system->addRule($rule);

    $engine = new ScoringEngine($system);

    $prediction = Prediction::create(
        userId: 'user-uuid',
        leagueId: 'league-uuid',
        type: PredictionType::PreStage,
        category: PredictionCategory::StageWinner,
        predictionValue: ['rider_id' => 'rider-1'],
        stageId: 'stage-uuid',
    );

    $result = StageResult::create('stage-uuid', 'rider-2', 1, '4:30:00');

    $event = $engine->calculateStageScore($prediction, $result, stageDifficulty: 1);

    expect($event->points)->toBe(0);
    expect($event->isZero())->toBeTrue();
    expect($event->description)->toContain('Fallo');
});

test('calculates total score from events', function () {
    $system = ScoringSystem::create('Test', ScoringSystemType::Standard, 'Test');
    $engine = new ScoringEngine($system);

    $events = collect([
        ScoreEvent::create('user-uuid', 'league-uuid', 'rule-1', 50, 'Test', 'context'),
        ScoreEvent::create('user-uuid', 'league-uuid', 'rule-2', 30, 'Test', 'context'),
        ScoreEvent::create('user-uuid', 'league-uuid', 'rule-3', 20, 'Test', 'context'),
    ]);

    $total = $engine->calculateTotalScore($events);

    expect($total)->toBe(100);
});

test('calculates gc top 5 exact match', function () {
    $system = ScoringSystem::create('Test', ScoringSystemType::Standard, 'Test');
    $system = $system
        ->addRule(ScoringRule::create($system->id, ScoringRuleType::GcTop5, 100, position: 1))
        ->addRule(ScoringRule::create($system->id, ScoringRuleType::GcTop5, 75, position: 2))
        ->addRule(ScoringRule::create($system->id, ScoringRuleType::GcTop5, 50, position: 3))
        ->addRule(ScoringRule::create($system->id, ScoringRuleType::GcTop5, 30, position: 4))
        ->addRule(ScoringRule::create($system->id, ScoringRuleType::GcTop5, 20, position: 5))
        ->addRule(ScoringRule::create($system->id, ScoringRuleType::GcTop5Partial, 15));

    $engine = new ScoringEngine($system);

    $prediction = Prediction::create(
        userId: 'user-uuid',
        leagueId: 'league-uuid',
        type: PredictionType::PreRace,
        category: PredictionCategory::GcTop5,
        predictionValue: ['rider_ids' => [
            0 => 'rider-1',
            1 => 'rider-2',
            2 => 'rider-3',
            3 => 'rider-4',
            4 => 'rider-5',
        ]],
    );

    $events = $engine->calculateGcTop5Score($prediction, [
        0 => 'rider-1',
        1 => 'rider-2',
        2 => 'rider-3',
        3 => 'rider-4',
        4 => 'rider-5',
    ]);

    expect($events)->toHaveCount(5);
    expect($events[0]->points)->toBe(100);
    expect($events[1]->points)->toBe(75);
    expect($events[2]->points)->toBe(50);
    expect($events[3]->points)->toBe(30);
    expect($events[4]->points)->toBe(20);
});

test('calculates gc top 5 partial match', function () {
    $system = ScoringSystem::create('Test', ScoringSystemType::Standard, 'Test');
    $system = $system
        ->addRule(ScoringRule::create($system->id, ScoringRuleType::GcTop5, 100, position: 1))
        ->addRule(ScoringRule::create($system->id, ScoringRuleType::GcTop5, 75, position: 2))
        ->addRule(ScoringRule::create($system->id, ScoringRuleType::GcTop5, 50, position: 3))
        ->addRule(ScoringRule::create($system->id, ScoringRuleType::GcTop5, 30, position: 4))
        ->addRule(ScoringRule::create($system->id, ScoringRuleType::GcTop5, 20, position: 5))
        ->addRule(ScoringRule::create($system->id, ScoringRuleType::GcTop5Partial, 15));

    $engine = new ScoringEngine($system);

    $prediction = Prediction::create(
        userId: 'user-uuid',
        leagueId: 'league-uuid',
        type: PredictionType::PreRace,
        category: PredictionCategory::GcTop5,
        predictionValue: ['rider_ids' => [
            0 => 'rider-a',
            1 => 'rider-b',
            2 => 'rider-c',
            3 => 'rider-d',
            4 => 'rider-e',
        ]],
    );

    $events = $engine->calculateGcTop5Score($prediction, [
        0 => 'rider-1',
        1 => 'rider-2',
        2 => 'rider-a',
        3 => 'rider-4',
        4 => 'rider-e',
    ]);

    expect($events)->toHaveCount(2);
    expect($events[0]->points)->toBe(15);
    expect($events[0]->description)->toContain('parcial');
    expect($events[1]->points)->toBe(20);
    expect($events[1]->description)->toContain('exacto');
});

test('calculates simple prediction score', function () {
    $system = ScoringSystem::create('Test', ScoringSystemType::Standard, 'Test');
    $rule = ScoringRule::create($system->id, ScoringRuleType::SuperCombativo, 30);
    $system = $system->addRule($rule);

    $engine = new ScoringEngine($system);

    $prediction = Prediction::create(
        userId: 'user-uuid',
        leagueId: 'league-uuid',
        type: PredictionType::PreRace,
        category: PredictionCategory::SuperCombativo,
        predictionValue: ['rider_id' => 'rider-1'],
    );

    $event = $engine->calculateSimpleScore($prediction, 'rider-1', ScoringRuleType::SuperCombativo);

    expect($event->points)->toBe(30);
    expect($event->isPositive())->toBeTrue();
});
