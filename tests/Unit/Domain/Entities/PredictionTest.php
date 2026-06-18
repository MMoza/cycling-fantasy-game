<?php

declare(strict_types=1);

use App\Domain\Entities\Prediction;
use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\PredictionType;

test('creates pre-race prediction', function () {
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

    expect($prediction->id)->toBeUuid();
    expect($prediction->userId)->toBe('user-uuid');
    expect($prediction->leagueId)->toBe('league-uuid');
    expect($prediction->type)->toBe(PredictionType::PreRace);
    expect($prediction->category)->toBe(PredictionCategory::GcTop5);
    expect($prediction->stageId)->toBeNull();
    expect($prediction->predictionValue)->toHaveCount(5);
    expect($prediction->isLocked())->toBeFalse();
});

test('creates pre-stage prediction', function () {
    $prediction = Prediction::create(
        userId: 'user-uuid',
        leagueId: 'league-uuid',
        type: PredictionType::PreStage,
        category: PredictionCategory::StageWinner,
        predictionValue: ['rider_id' => 'rider-1'],
        stageId: 'stage-uuid',
    );

    expect($prediction->stageId)->toBe('stage-uuid');
    expect($prediction->type)->toBe(PredictionType::PreStage);
});

test('locks prediction', function () {
    $prediction = Prediction::create(
        userId: 'user-uuid',
        leagueId: 'league-uuid',
        type: PredictionType::PreRace,
        category: PredictionCategory::GcTop5,
        predictionValue: ['1' => 'rider-1'],
    );

    $locked = $prediction->lock('2026-07-01 10:00:00');

    expect($locked->isLocked())->toBeTrue();
    expect($locked->lockedAt)->toBe('2026-07-01 10:00:00');
    expect($locked->id)->toBe($prediction->id);
});

test('revealed prediction is locked', function () {
    $prediction = Prediction::create(
        userId: 'user-uuid',
        leagueId: 'league-uuid',
        type: PredictionType::PreRace,
        category: PredictionCategory::GcTop5,
        predictionValue: ['1' => 'rider-1'],
    );

    $locked = $prediction->lock('2026-07-01 10:00:00');

    expect($locked->isRevealed())->toBeTrue();
});
