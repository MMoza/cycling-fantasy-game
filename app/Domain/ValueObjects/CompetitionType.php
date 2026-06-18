<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum CompetitionType: string
{
    case GrandTour = 'grand_tour';
    case WeekTour = 'week_tour';
    case Classic = 'classic';

    public function label(): string
    {
        return match ($this) {
            self::GrandTour => 'Gran Vuelta',
            self::WeekTour => 'Vuelta de una semana',
            self::Classic => 'Clásica',
        };
    }
}
