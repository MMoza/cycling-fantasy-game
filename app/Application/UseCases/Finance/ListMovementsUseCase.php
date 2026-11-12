<?php

declare(strict_types=1);

namespace App\Application\UseCases\Finance;

use App\Application\DTOs\Finance\MovementDTO;
use App\Application\DTOs\View\AdminFinancePageDTO;
use App\Application\Exceptions\ApplicationException;
use App\Domain\Entities\FinancialMovement;
use App\Domain\Interfaces\FinancialMovementRepositoryInterface;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementFilters;
use App\Domain\ValueObjects\MovementType;

/**
 * Listado paginado de movimientos financieros con filtros combinables
 * (año, tipo, categoría y rango de fechas), ordenado por fecha económica
 * descendente y paginado de 50 en 50. Compone el resumen financiero para
 * servir la página completa de administración.
 */
final class ListMovementsUseCase
{
    private const PER_PAGE = 50;

    public function __construct(
        private readonly GetFinanceSummaryUseCase $summaryUseCase,
        private readonly FinancialMovementRepositoryInterface $movements,
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * @throws ApplicationException cuando el rango de fechas no es válido
     */
    public function execute(MovementFilters $filters): AdminFinancePageDTO
    {
        $this->assertValidRange($filters);

        $result = $this->movements->search($filters, self::PER_PAGE);
        $creatorNames = $this->creatorNames($result->items);

        $rows = array_map(
            fn (FinancialMovement $movement): array => MovementDTO::fromEntity(
                $movement,
                $movement->createdBy !== null ? $creatorNames[$movement->createdBy] ?? null : null,
            )->toArray(),
            $result->items,
        );

        return new AdminFinancePageDTO(
            summary: $this->summaryUseCase->execute($filters->year),
            movements: [
                'data' => $rows,
                'current_page' => $result->currentPage,
                'last_page' => $result->lastPage(),
                'per_page' => $result->perPage,
                'total' => $result->total,
            ],
            filters: [
                'year' => $filters->year,
                'type' => $filters->type?->value,
                'category' => $filters->category?->value,
                'from' => $filters->from?->format('Y-m-d'),
                'to' => $filters->to?->format('Y-m-d'),
                'page' => $result->currentPage,
            ],
            categories: $this->availableCategories(),
        );
    }

    /**
     * @throws ApplicationException cuando el rango de fechas no es válido
     */
    private function assertValidRange(MovementFilters $filters): void
    {
        if ($filters->from !== null && $filters->to !== null && $filters->from > $filters->to) {
            throw new ApplicationException(
                'El rango de fechas no es válido: la fecha inicial debe ser anterior o igual a la final',
            );
        }
    }

    /**
     * @param  list<FinancialMovement>  $movements
     * @return array<string, string>
     */
    private function creatorNames(array $movements): array
    {
        $ids = [];

        foreach ($movements as $movement) {
            if ($movement->createdBy !== null) {
                $ids[$movement->createdBy] = true;
            }
        }

        if ($ids === []) {
            return [];
        }

        $names = [];

        foreach ($this->users->findMany(array_keys($ids)) as $user) {
            $names[$user->id] = $user->name;
        }

        return $names;
    }

    /**
     * @return list<array{value: string, label: string, type: string}>
     */
    private function availableCategories(): array
    {
        $categories = [];

        foreach (MovementType::cases() as $type) {
            foreach (MovementCategory::forType($type) as $category) {
                $categories[] = [
                    'value' => $category->value,
                    'label' => $category->label($type),
                    'type' => $type->value,
                ];
            }
        }

        return $categories;
    }
}
