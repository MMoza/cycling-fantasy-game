<?php

declare(strict_types=1);

namespace App\Application\UseCases\Finance;

use App\Application\Exceptions\ApplicationException;
use App\Infrastructure\Persistence\Models\FinancialMovementModel;
use App\Models\User;

/**
 * Elimina un movimiento financiero. La operación no puede ejecutarse sin
 * autorización: solo los superadministradores pueden borrar movimientos.
 */
final class DeleteMovementUseCase
{
    /**
     * @throws ApplicationException cuando el usuario no está autorizado
     */
    public function execute(string $id, User $actor): void
    {
        if (! $actor->is_admin) {
            throw new ApplicationException('No tienes permisos para eliminar movimientos financieros');
        }

        FinancialMovementModel::findOrFail($id)->delete();
    }
}
