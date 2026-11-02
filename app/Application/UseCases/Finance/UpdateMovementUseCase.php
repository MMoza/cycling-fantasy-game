<?php

declare(strict_types=1);

namespace App\Application\UseCases\Finance;

use App\Application\DTOs\Finance\UpdateMovementDTO;
use App\Application\Exceptions\ApplicationException;
use App\Application\Services\MovementValidationService;
use App\Infrastructure\Persistence\Models\FinancialMovementModel;

/**
 * Modifica los campos editables de un movimiento financiero. La
 * identificación, el origen, el estado y el creador del movimiento se
 * mantienen intactos.
 */
final class UpdateMovementUseCase
{
    public function __construct(private readonly MovementValidationService $validation) {}

    /**
     * @throws ApplicationException cuando las reglas de negocio no se cumplen
     */
    public function execute(UpdateMovementDTO $dto): FinancialMovementModel
    {
        $movement = FinancialMovementModel::findOrFail($dto->id);
        $amount = $this->validation->validate($dto->type, $dto->amount, $dto->category);

        $movement->update([
            'type' => $dto->type,
            'amount' => $amount->toDecimalString(),
            'concept' => $dto->concept,
            'category' => $dto->category,
            'occurred_on' => $dto->occurredOn,
            'notes' => $dto->notes,
        ]);

        return $movement;
    }
}
