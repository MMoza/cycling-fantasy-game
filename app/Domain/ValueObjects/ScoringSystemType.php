<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum ScoringSystemType: string
{
    case Standard = 'standard';
    case Aggressive = 'aggressive';
    case Conservative = 'conservative';
    case OneWeek = 'one_week';
    case OneDay = 'one_day';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Estándar',
            self::Aggressive => 'Agresivo',
            self::Conservative => 'Conservador',
            self::OneWeek => 'Carrera de una semana',
            self::OneDay => 'Carrera de un día',
            self::Custom => 'Personalizado',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Standard => 'Puntuación equilibrada',
            self::Aggressive => 'Premia más al ganador, menos al resto',
            self::Conservative => 'Puntuación más repartida',
            self::OneWeek => 'Solo clasificación general sin maillots',
            self::OneDay => 'Predicciones por posición (1º a 10º)',
            self::Custom => 'Reglas personalizadas',
        };
    }
}
