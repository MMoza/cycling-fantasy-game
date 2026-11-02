<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Exceptions\ApplicationException;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementType;
use InvalidArgumentException;

/**
 * Reglas de negocio compartidas por la creación y la modificación de
 * movimientos financieros.
 */
final class MovementValidationService
{
    /**
     * Normaliza el importe y valida las reglas de negocio del movimiento.
     *
     * @throws ApplicationException cuando el movimiento no es válido
     */
    public function validate(MovementType $type, string $amount, MovementCategory $category): Money
    {
        try {
            $money = Money::fromDecimalString($amount);
        } catch (InvalidArgumentException) {
            throw new ApplicationException('El importe del movimiento no es válido');
        }

        if ($money->isNegative()) {
            throw new ApplicationException('El importe del movimiento no puede ser negativo');
        }

        if (! $category->supports($type)) {
            throw new ApplicationException(sprintf(
                'La categoría «%s» no es compatible con el tipo de movimiento «%s»',
                $category->label($type),
                $type->label(),
            ));
        }

        return $money;
    }
}
