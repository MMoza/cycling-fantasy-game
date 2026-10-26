<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum BadgeMode: string
{
    /** Nivel = mayor tier alcanzado por el contador (p.ej. 1/5/25 etapas disputadas). */
    case Cumulative = 'cumulative';

    /** Se otorga una única vez al llegar al objetivo; guarda progreso parcial (p.ej. 2/3). */
    case Once = 'once';
}
