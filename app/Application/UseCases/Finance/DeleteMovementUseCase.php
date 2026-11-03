<?php

declare(strict_types=1);

namespace App\Application\UseCases\Finance;

use App\Application\Exceptions\ApplicationException;
use App\Domain\Interfaces\FinancialMovementRepositoryInterface;
use App\Domain\Interfaces\UserRepositoryInterface;

/**
 * Elimina un movimiento financiero. La operación no puede ejecutarse sin
 * autorización: solo los superadministradores pueden borrar movimientos.
 */
final class DeleteMovementUseCase
{
    public function __construct(
        private readonly FinancialMovementRepositoryInterface $movements,
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * @throws ApplicationException cuando el usuario no está autorizado o el
     *                              movimiento no existe
     */
    public function execute(string $id, string $actorId): void
    {
        $actor = $this->users->find($actorId);

        if ($actor === null || ! $actor->isAdmin) {
            throw new ApplicationException('No tienes permisos para eliminar movimientos financieros');
        }

        if ($this->movements->find($id) === null) {
            throw new ApplicationException('El movimiento solicitado no existe');
        }

        $this->movements->delete($id);
    }
}
