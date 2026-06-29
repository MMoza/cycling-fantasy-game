<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum UserPlan: string
{
    case Free = 'free';
    case Premium = 'premium';

    public function maxLeagues(): int
    {
        return match ($this) {
            self::Free => 5,
            self::Premium => 999,
        };
    }

    public function maxPlayersPerLeague(): int
    {
        return match ($this) {
            self::Free => 10,
            self::Premium => 999,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Gratuito',
            self::Premium => 'Premium',
        };
    }
}
