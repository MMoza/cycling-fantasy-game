<?php

declare(strict_types=1);

use App\Domain\Entities\ScoreEvent;

test('creates score event', function () {
    $event = ScoreEvent::create(
        userId: 'user-uuid',
        leagueId: 'league-uuid',
        scoringRuleId: 'rule-uuid',
        points: 50,
        description: 'Ganador etapa 3',
        context: 'stage_winner',
    );

    expect($event->id)->toBeUuid();
    expect($event->userId)->toBe('user-uuid');
    expect($event->leagueId)->toBe('league-uuid');
    expect($event->points)->toBe(50);
    expect($event->description)->toBe('Ganador etapa 3');
    expect($event->context)->toBe('stage_winner');
});

test('identifies positive score', function () {
    $positive = ScoreEvent::create('user-uuid', 'league-uuid', 'rule-uuid', 50, 'Test', 'context');
    $negative = ScoreEvent::create('user-uuid', 'league-uuid', 'rule-uuid', -10, 'Test', 'context');
    $zero = ScoreEvent::create('user-uuid', 'league-uuid', 'rule-uuid', 0, 'Test', 'context');

    expect($positive->isPositive())->toBeTrue();
    expect($negative->isPositive())->toBeFalse();
    expect($zero->isPositive())->toBeFalse();
});

test('identifies negative score', function () {
    $positive = ScoreEvent::create('user-uuid', 'league-uuid', 'rule-uuid', 50, 'Test', 'context');
    $negative = ScoreEvent::create('user-uuid', 'league-uuid', 'rule-uuid', -10, 'Test', 'context');
    $zero = ScoreEvent::create('user-uuid', 'league-uuid', 'rule-uuid', 0, 'Test', 'context');

    expect($positive->isNegative())->toBeFalse();
    expect($negative->isNegative())->toBeTrue();
    expect($zero->isNegative())->toBeFalse();
});

test('identifies zero score', function () {
    $positive = ScoreEvent::create('user-uuid', 'league-uuid', 'rule-uuid', 50, 'Test', 'context');
    $negative = ScoreEvent::create('user-uuid', 'league-uuid', 'rule-uuid', -10, 'Test', 'context');
    $zero = ScoreEvent::create('user-uuid', 'league-uuid', 'rule-uuid', 0, 'Test', 'context');

    expect($positive->isZero())->toBeFalse();
    expect($negative->isZero())->toBeFalse();
    expect($zero->isZero())->toBeTrue();
});
