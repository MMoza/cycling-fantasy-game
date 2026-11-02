<?php

declare(strict_types=1);

namespace App\Application\UseCases\Finance;

use App\Application\DTOs\Finance\MovementDTO;
use App\Application\DTOs\Finance\MovementFiltersDTO;
use App\Application\DTOs\View\AdminFinancePageDTO;
use App\Application\Exceptions\ApplicationException;
use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementType;
use App\Infrastructure\Persistence\Models\FinancialMovementModel;

/**
 * Listado paginado de movimientos financieros con filtros combinables
 * (año, tipo, categoría y rango de fechas), ordenado por fecha económica
 * descendente y paginado de 50 en 50. Compone el resumen financiero para
 * servir la página completa de administración.
 */
final class ListMovementsUseCase
{
    private const PER_PAGE = 50;

    public function __construct(private readonly GetFinanceSummaryUseCase $summaryUseCase) {}

    /**
     * @throws ApplicationException cuando el rango de fechas no es válido
     */
    public function execute(MovementFiltersDTO $filters): AdminFinancePageDTO
    {
        $this->assertValidRange($filters);

        $query = FinancialMovementModel::query()->with('creator');

        $query->whereDate('occurred_on', '>=', sprintf('%04d-01-01', $filters->year));
        $query->whereDate('occurred_on', '<=', sprintf('%04d-12-31', $filters->year));

        if ($filters->type !== null) {
            $query->where('type', $filters->type->value);
        }

        if ($filters->category !== null) {
            $query->where('category', $filters->category->value);
        }

        if ($filters->from !== null) {
            $query->whereDate('occurred_on', '>=', $filters->from);
        }

        if ($filters->to !== null) {
            $query->whereDate('occurred_on', '<=', $filters->to);
        }

        $paginator = $query
            ->orderByDesc('occurred_on')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE, ['*'], 'page', $filters->page)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn (FinancialMovementModel $movement): array => MovementDTO::fromModel($movement)->toArray())
        );

        return new AdminFinancePageDTO(
            summary: $this->summaryUseCase->execute($filters->year),
            movements: $paginator->toArray(),
            filters: [
                'year' => $filters->year,
                'type' => $filters->type?->value,
                'category' => $filters->category?->value,
                'from' => $filters->from,
                'to' => $filters->to,
                'page' => $paginator->currentPage(),
            ],
            categories: $this->availableCategories(),
        );
    }

    /**
     * @throws ApplicationException cuando el rango de fechas no es válido
     */
    private function assertValidRange(MovementFiltersDTO $filters): void
    {
        if ($filters->from !== null && $filters->to !== null && $filters->from > $filters->to) {
            throw new ApplicationException(
                'El rango de fechas no es válido: la fecha inicial debe ser anterior o igual a la final',
            );
        }
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
