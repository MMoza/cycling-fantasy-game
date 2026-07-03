<?php

declare(strict_types=1);

use App\Domain\ValueObjects\CompetitionType;

test('returns correct label for grand tour (GC)', function () {
    expect(CompetitionType::GC->label())->toBe('Gran Vuelta');
});

test('returns correct label for major', function () {
    expect(CompetitionType::Major->label())->toBe('Carrera importante');
});

test('returns correct label for monument', function () {
    expect(CompetitionType::Monument->label())->toBe('Monumento');
});

test('returns correct label for classic', function () {
    expect(CompetitionType::Classic->label())->toBe('Clásica');
});

test('returns correct label for championship', function () {
    expect(CompetitionType::Championship->label())->toBe('Campeonato');
});

test('has correct string value', function () {
    expect(CompetitionType::GC->value)->toBe('gc');
    expect(CompetitionType::Major->value)->toBe('major');
    expect(CompetitionType::Monument->value)->toBe('monument');
    expect(CompetitionType::Classic->value)->toBe('classic');
    expect(CompetitionType::Championship->value)->toBe('championship');
});
