<?php

declare(strict_types=1);

use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;

test('stage type returns correct label', function () {
    expect(StageType::Flat->label())->toBe('Llano');
    expect(StageType::Mountain->label())->toBe('Montaña');
    expect(StageType::HighMountain->label())->toBe('Alta montaña');
    expect(StageType::Hill->label())->toBe('Media montaña');
    expect(StageType::TimeTrial->label())->toBe('Contrarreloj individual');
    expect(StageType::TeamTimeTrial->label())->toBe('Contrarreloj por equipos');
    expect(StageType::Rest->label())->toBe('Descanso');
});

test('stage status returns correct label', function () {
    expect(StageStatus::Upcoming->label())->toBe('Próxima');
    expect(StageStatus::Ongoing->label())->toBe('En curso');
    expect(StageStatus::Finished->label())->toBe('Finalizada');
});
