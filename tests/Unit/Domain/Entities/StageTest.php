<?php

declare(strict_types=1);

use App\Domain\Entities\Stage;
use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;

test('creates stage with factory method', function () {
    $stage = Stage::create(
        editionId: 'edition-uuid',
        number: 1,
        name: 'Etapa 1',
        date: '2026-07-01',
        type: StageType::Flat,
        distance: 180.5,
        origin: 'Lille',
        destination: 'Paris',
    );

    expect($stage->id)->toBeUuid();
    expect($stage->editionId)->toBe('edition-uuid');
    expect($stage->number)->toBe(1);
    expect($stage->name)->toBe('Etapa 1');
    expect($stage->date)->toBe('2026-07-01');
    expect($stage->type)->toBe(StageType::Flat);
    expect($stage->distance)->toBe(180.5);
    expect($stage->origin)->toBe('Lille');
    expect($stage->destination)->toBe('Paris');
    expect($stage->status)->toBe(StageStatus::Upcoming);
});

test('starts stage', function () {
    $stage = Stage::create(
        editionId: 'edition-uuid',
        number: 1,
        name: 'Etapa 1',
        date: '2026-07-01',
        type: StageType::Flat,
        distance: 180.5,
        origin: 'Lille',
        destination: 'Paris',
    );

    $started = $stage->start();

    expect($started->status)->toBe(StageStatus::Ongoing);
    expect($started->id)->toBe($stage->id);
});

test('finishes stage', function () {
    $stage = Stage::create(
        editionId: 'edition-uuid',
        number: 1,
        name: 'Etapa 1',
        date: '2026-07-01',
        type: StageType::Flat,
        distance: 180.5,
        origin: 'Lille',
        destination: 'Paris',
    );

    $started = $stage->start();
    $finished = $started->finish();

    expect($finished->status)->toBe(StageStatus::Finished);
    expect($finished->id)->toBe($stage->id);
});
