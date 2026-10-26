<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

/**
 * Número que se superpone al icono del badge.
 */
enum BadgeDisplay: string
{
    /** Número de veces conseguido (p.ej. 2 victorias en el Tour). */
    case Times = 'times';

    /** Nivel alcanzado (p.ej. Nivel 2 de 5). */
    case Level = 'level';

    /** Sin número: logros únicos o especiales. */
    case None = 'none';
}
