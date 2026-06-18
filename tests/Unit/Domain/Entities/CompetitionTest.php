<?php

declare(strict_types=1);

use App\Domain\Entities\Competition;
use App\Domain\ValueObjects\CompetitionType;

test('creates competition with factory method', function () {
    $competition = Competition::create(
        name: 'Tour de Francia',
        type: CompetitionType::GrandTour,
        country: 'Francia',
    );

    expect($competition->id)->toBeUuid();
    expect($competition->name)->toBe('Tour de Francia');
    expect($competition->type)->toBe(CompetitionType::GrandTour);
    expect($competition->country)->toBe('Francia');
    expect($competition->active)->toBeTrue();
});

test('deactivates competition', function () {
    $competition = Competition::create(
        name: 'Tour de Francia',
        type: CompetitionType::GrandTour,
        country: 'Francia',
    );

    $deactivated = $competition->deactivate();

    expect($deactivated->active)->toBeFalse();
    expect($deactivated->id)->toBe($competition->id);
});

test('activates competition', function () {
    $competition = Competition::create(
        name: 'Tour de Francia',
        type: CompetitionType::GrandTour,
        country: 'Francia',
    );

    $deactivated = $competition->deactivate();
    $activated = $deactivated->activate();

    expect($activated->active)->toBeTrue();
    expect($activated->id)->toBe($competition->id);
});
