<?php

declare(strict_types=1);

namespace App\Application\UseCases\Finance;

use App\Application\DTOs\Finance\StoreMovementDTO;
use App\Application\Exceptions\ApplicationException;
use App\Application\Services\MovementValidationService;
use App\Domain\Entities\FinancialMovement;
use App\Domain\Interfaces\FinancialMovementRepositoryInterface;

/**
 * Da de alta un movimiento financiero manual: el origen es siempre "manual",
 * el estado "confirmed" y se asocia el usuario autenticado como creador.
 */
final class CreateMovementUseCase
{
    public function __construct(
        private readonly MovementValidationService $validation,
        private readonly FinancialMovementRepositoryInterface $movements,
    ) {}

    /**
     * @throws ApplicationException cuando las reglas de negocio no se cumplen
     */
    public function execute(StoreMovementDTO $dto): FinancialMovement
    {
        $amount = $this->validation->validate($dto->type, $dto->amount, $dto->category);

        $movement = FinancialMovement::create(
            type: $dto->type,
            amount: $amount,
            concept: $dto->concept,
            category: $dto->category,
            occurredOn: $dto->occurredOn,
            notes: $dto->notes,
            createdBy: $dto->createdBy,
        );

        $this->movements->save($movement);

        return $movement;
    }
}
