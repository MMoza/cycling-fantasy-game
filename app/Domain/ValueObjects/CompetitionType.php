<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum CompetitionType: string
{
    case GC = 'gc';
    case Major = 'major';
    case Monument = 'monument';
    case Classic = 'classic';
    case Championship = 'championship';

    public function label(): string
    {
        return match ($this) {
            self::GC => 'Gran Vuelta',
            self::Major => 'Carrera importante',
            self::Monument => 'Monumento',
            self::Classic => 'Clásica',
            self::Championship => 'Campeonato',
        };
    }
}
