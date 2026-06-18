<?php

declare(strict_types=1);

use App\Domain\ValueObjects\CompetitionType;

test('returns correct label for grand tour', function () {
    expect(CompetitionType::GrandTour->label())->toBe('Gran Vuelta');
});

test('returns correct label for week tour', function () {
    expect(CompetitionType::WeekTour->label())->toBe('Vuelta de una semana');
});

test('returns correct label for classic', function () {
    expect(CompetitionType::Classic->label())->toBe('Clásica');
});

test('has correct string value', function () {
    expect(CompetitionType::GrandTour->value)->toBe('grand_tour');
    expect(CompetitionType::WeekTour->value)->toBe('week_tour');
    expect(CompetitionType::Classic->value)->toBe('classic');
});
