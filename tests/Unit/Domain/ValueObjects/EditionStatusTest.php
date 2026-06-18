<?php

declare(strict_types=1);

use App\Domain\ValueObjects\EditionStatus;

test('returns correct label for upcoming', function () {
    expect(EditionStatus::Upcoming->label())->toBe('Próxima');
});

test('returns correct label for ongoing', function () {
    expect(EditionStatus::Ongoing->label())->toBe('En curso');
});

test('returns correct label for finished', function () {
    expect(EditionStatus::Finished->label())->toBe('Finalizada');
});
