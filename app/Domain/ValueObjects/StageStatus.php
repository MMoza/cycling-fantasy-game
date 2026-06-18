<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum StageStatus: string
{
    case Upcoming = 'upcoming';
    case Ongoing = 'ongoing';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::Upcoming => 'Próxima',
            self::Ongoing => 'En curso',
            self::Finished => 'Finalizada',
        };
    }
}
