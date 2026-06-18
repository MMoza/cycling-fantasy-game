<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum ScoringRuleContext: string
{
    case PreRace = 'pre_race';
    case PreStage = 'pre_stage';

    public function label(): string
    {
        return match ($this) {
            self::PreRace => 'Antes de la carrera',
            self::PreStage => 'Antes de cada etapa',
        };
    }
}
