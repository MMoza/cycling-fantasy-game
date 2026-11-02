<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

/**
 * Estado de un movimiento financiero. Solo los movimientos "confirmed"
 * participan en los cálculos del resumen financiero; los pendientes se
 * muestran en el listado administrativo pero no alteran el balance.
 */
enum MovementStatus: string
{
    case Confirmed = 'confirmed';
    case Pending = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::Confirmed => 'Confirmado',
            self::Pending => 'Pendiente',
        };
    }
}
