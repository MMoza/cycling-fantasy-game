<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum ScoringSystemType: string
{
    case Standard = 'standard';
    case Aggressive = 'aggressive';
    case Conservative = 'conservative';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Estándar',
            self::Aggressive => 'Agresivo',
            self::Conservative => 'Conservador',
            self::Custom => 'Personalizado',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Standard => 'Puntuación equilibrada',
            self::Aggressive => 'Premia más al ganador, menos al resto',
            self::Conservative => 'Puntuación más repartida',
            self::Custom => 'Reglas personalizadas',
        };
    }
}
