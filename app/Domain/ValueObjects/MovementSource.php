<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

/**
 * Origen de un movimiento financiero. Los movimientos creados desde la
 * administración son "manual"; "imported" y "api" quedan reservados para
 * las importaciones automatizadas (FIN-005).
 */
enum MovementSource: string
{
    case Manual = 'manual';
    case Imported = 'imported';
    case Api = 'api';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::Imported => 'Importado',
            self::Api => 'API',
        };
    }
}
