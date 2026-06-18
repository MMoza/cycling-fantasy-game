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
    $rule = ScoringRule::create($system->id, ScoringRuleType::StageWinner, 50);
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

    $event = $engine->calculateStageScore($prediction, $result);

    expect($event->points)->toBe(50);
    expect($event->isPositive())->toBeTrue();
    expect($event->description)->toContain('Acierto');
});

test('calculates incorrect stage winner prediction', function () {
    $system = ScoringSystem::create('Test', ScoringSystemType::Standard, 'Test');
    $rule = ScoringRule::create($system->id, ScoringRuleType::StageWinner, 50);
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

    $event = $engine->calculateStageScore($prediction, $result);

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

test('calculates gc top 5 prediction', function () {
    $system = ScoringSystem::create('Test', ScoringSystemType::Standard, 'Test');
    $rule = ScoringRule::create($system->id, ScoringRuleType::GcTop5, 100);
    $system = $system->addRule($rule);

    $engine = new ScoringEngine($system);

    $prediction = Prediction::create(
        userId: 'user-uuid',
        leagueId: 'league-uuid',
        type: PredictionType::PreRace,
        category: PredictionCategory::GcTop5,
        predictionValue: [
            '1' => 'rider-1',
            '2' => 'rider-2',
            '3' => 'rider-3',
            '4' => 'rider-4',
            '5' => 'rider-5',
        ],
    );

    $event = $engine->calculateGcScore($prediction, 'rider-1', 1);

    expect($event->points)->toBe(100);
    expect($event->description)->toContain('Acierto');
});

test('calculates incorrect gc top 5 prediction', function () {
    $system = ScoringSystem::create('Test', ScoringSystemType::Standard, 'Test');
    $rule = ScoringRule::create($system->id, ScoringRuleType::GcTop5, 100);
    $system = $system->addRule($rule);

    $engine = new ScoringEngine($system);

    $prediction = Prediction::create(
        userId: 'user-uuid',
        leagueId: 'league-uuid',
        type: PredictionType::PreRace,
        category: PredictionCategory::GcTop5,
        predictionValue: [
            '1' => 'rider-1',
            '2' => 'rider-2',
            '3' => 'rider-3',
            '4' => 'rider-4',
            '5' => 'rider-5',
        ],
    );

    $event = $engine->calculateGcScore($prediction, 'rider-99', 1);

    expect($event->points)->toBe(0);
    expect($event->description)->toContain('Fallo');
});
