<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum StageType: string
{
    case Flat = 'flat';
    case Mountain = 'mountain';
    case HighMountain = 'high_mountain';
    case Hill = 'hill';
    case TimeTrial = 'time_trial';
    case TeamTimeTrial = 'team_time_trial';
    case Rest = 'rest';

    public function label(): string
    {
        return match ($this) {
            self::Flat => 'Llano',
            self::Mountain => 'Montaña',
            self::HighMountain => 'Alta montaña',
            self::Hill => 'Media montaña',
            self::TimeTrial => 'Contrarreloj individual',
            self::TeamTimeTrial => 'Contrarreloj por equipos',
            self::Rest => 'Descanso',
        };
    }
}
