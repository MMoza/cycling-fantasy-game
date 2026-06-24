<?php

declare(strict_types=1);

use App\Domain\Entities\StageResult;

test('creates stage result with factory method', function () {
    $result = StageResult::create(
        stageId: 'stage-uuid',
        riderId: 'rider-uuid',
        position: 1,
        time: '4:30:15',
        gap: null,
    );

    expect($result->id)->toBeUuid();
    expect($result->stageId)->toBe('stage-uuid');
    expect($result->riderId)->toBe('rider-uuid');
    expect($result->position)->toBe(1);
    expect($result->time)->toBe('4:30:15');
    expect($result->gap)->toBeNull();
});

test('identifies winner', function () {
    $winner = StageResult::create('stage-uuid', 'rider-uuid', 1);
    $second = StageResult::create('stage-uuid', 'rider-uuid', 2);

    expect($winner->isWinner())->toBeTrue();
    expect($second->isWinner())->toBeFalse();
});

test('identifies podium', function () {
    $first = StageResult::create('stage-uuid', 'rider-uuid', 1);
    $second = StageResult::create('stage-uuid', 'rider-uuid', 2);
    $third = StageResult::create('stage-uuid', 'rider-uuid', 3);
    $fourth = StageResult::create('stage-uuid', 'rider-uuid', 4);

    expect($first->isPodium())->toBeTrue();
    expect($second->isPodium())->toBeTrue();
    expect($third->isPodium())->toBeTrue();
    expect($fourth->isPodium())->toBeFalse();
});

test('identifies top five', function () {
    $fifth = StageResult::create('stage-uuid', 'rider-uuid', 5);
    $sixth = StageResult::create('stage-uuid', 'rider-uuid', 6);

    expect($fifth->isTopFive())->toBeTrue();
    expect($sixth->isTopFive())->toBeFalse();
});

test('creates stage result with gc leader and combativo flags', function () {
    $result = StageResult::create(
        stageId: 'stage-uuid',
        riderId: 'rider-uuid',
        position: 1,
        isGcLeader: true,
        isCombativo: true,
    );

    expect($result->isGcLeader)->toBeTrue();
    expect($result->isCombativo)->toBeTrue();
});
