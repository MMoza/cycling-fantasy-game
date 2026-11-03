<?php

declare(strict_types=1);

namespace App\Application\UseCases\Finance;

use App\Application\DTOs\Finance\UpdateMovementDTO;
use App\Application\Exceptions\ApplicationException;
use App\Application\Services\MovementValidationService;
use App\Domain\Entities\FinancialMovement;
use App\Domain\Interfaces\FinancialMovementRepositoryInterface;

/**
 * Modifica los campos editables de un movimiento financiero. La
 * identificación, el origen, el estado y el creador del movimiento se
 * mantienen intactos.
 */
final class UpdateMovementUseCase
{
    public function __construct(
        private readonly MovementValidationService $validation,
        private readonly FinancialMovementRepositoryInterface $movements,
    ) {}

    /**
     * @throws ApplicationException cuando el movimiento no existe o las reglas
     *                              de negocio no se cumplen
     */
    public function execute(UpdateMovementDTO $dto): FinancialMovement
    {
        $movement = $this->movements->find($dto->id);

        if ($movement === null) {
            throw new ApplicationException('El movimiento solicitado no existe');
        }

        $amount = $this->validation->validate($dto->type, $dto->amount, $dto->category);

        $updated = $movement->update(
            type: $dto->type,
            amount: $amount,
            concept: $dto->concept,
            category: $dto->category,
            occurredOn: $dto->occurredOn,
            notes: $dto->notes,
        );

        $this->movements->save($updated);

        return $updated;
    }
}
