<?php

declare(strict_types=1);

use App\Domain\Entities\Edition;
use App\Domain\ValueObjects\EditionStatus;

test('creates edition with factory method', function () {
    $edition = Edition::create(
        competitionId: 'comp-uuid',
        year: 2026,
        startDate: '2026-07-01',
        endDate: '2026-07-23',
    );

    expect($edition->id)->toBeUuid();
    expect($edition->competitionId)->toBe('comp-uuid');
    expect($edition->year)->toBe(2026);
    expect($edition->startDate)->toBe('2026-07-01');
    expect($edition->endDate)->toBe('2026-07-23');
    expect($edition->status)->toBe(EditionStatus::Upcoming);
});

test('starts edition', function () {
    $edition = Edition::create(
        competitionId: 'comp-uuid',
        year: 2026,
        startDate: '2026-07-01',
        endDate: '2026-07-23',
    );

    $started = $edition->start();

    expect($started->status)->toBe(EditionStatus::Ongoing);
    expect($started->id)->toBe($edition->id);
});

test('finishes edition', function () {
    $edition = Edition::create(
        competitionId: 'comp-uuid',
        year: 2026,
        startDate: '2026-07-01',
        endDate: '2026-07-23',
    );

    $started = $edition->start();
    $finished = $started->finish();

    expect($finished->status)->toBe(EditionStatus::Finished);
    expect($finished->id)->toBe($edition->id);
});
