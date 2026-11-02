<?php

declare(strict_types=1);

namespace App\Application\UseCases\Finance;

use App\Application\DTOs\Finance\StoreMovementDTO;
use App\Application\Exceptions\ApplicationException;
use App\Application\Services\MovementValidationService;
use App\Domain\ValueObjects\MovementSource;
use App\Domain\ValueObjects\MovementStatus;
use App\Infrastructure\Persistence\Models\FinancialMovementModel;
use Illuminate\Support\Str;

/**
 * Da de alta un movimiento financiero manual: el origen es siempre "manual",
 * el estado "confirmed" y se asocia el usuario autenticado como creador.
 */
final class CreateMovementUseCase
{
    public function __construct(private readonly MovementValidationService $validation) {}

    /**
     * @throws ApplicationException cuando las reglas de negocio no se cumplen
     */
    public function execute(StoreMovementDTO $dto): FinancialMovementModel
    {
        $amount = $this->validation->validate($dto->type, $dto->amount, $dto->category);

        return FinancialMovementModel::create([
            'id' => Str::uuid()->toString(),
            'type' => $dto->type,
            'amount' => $amount->toDecimalString(),
            'concept' => $dto->concept,
            'category' => $dto->category,
            'occurred_on' => $dto->occurredOn,
            'source' => MovementSource::Manual,
            'external_id' => null,
            'status' => MovementStatus::Confirmed,
            'notes' => $dto->notes,
            'created_by' => $dto->createdBy,
        ]);
    }
}
