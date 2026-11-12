<?php

declare(strict_types=1);

namespace App\Application\UseCases\Finance;

use App\Application\DTOs\Finance\MovementDTO;
use App\Domain\Entities\FinancialMovement;
use App\Domain\Interfaces\FinanceSummaryQueryInterface;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Domain\ValueObjects\FinanceCategoryTotal;

/**
 * Resumen financiero de una temporada (año natural). Es la fuente común de
 * los cálculos financieros de la administración, del dashboard avanzado
 * (FIN-003) y de la vista pública (FIN-006): las reglas financieras no se
 * duplican en ningún otro punto.
 *
 * Solo los movimientos "confirmed" participan en los cálculos; los
 * pendientes no alteran el balance confirmado.
 */
final class GetFinanceSummaryUseCase
{
    public function __construct(
        private readonly FinanceSummaryQueryInterface $summaryQuery,
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * @return array{
     *     year: int,
     *     period: array{from: string, to: string},
     *     income_total: string,
     *     expense_total: string,
     *     balance: string,
     *     by_category: list<array{type: string, category: string, label: string, total: string, count: int}>,
     *     top_expenses: list<array<string, mixed>>,
     *     movements_count: int,
     * }
     */
    public function execute(int $year): array
    {
        $summary = $this->summaryQuery->summarizeYear($year);
        $creatorNames = $this->creatorNames($summary->topExpenses);

        return [
            'year' => $summary->year,
            'period' => [
                'from' => sprintf('%04d-01-01', $year),
                'to' => sprintf('%04d-12-31', $year),
            ],
            'income_total' => $summary->incomeTotal->toDecimalString(),
            'expense_total' => $summary->expenseTotal->toDecimalString(),
            'balance' => $summary->balance()->toDecimalString(),
            'by_category' => array_map(
                fn (FinanceCategoryTotal $total): array => [
                    'type' => $total->type->value,
                    'category' => $total->category->value,
                    'label' => $total->category->label($total->type),
                    'total' => $total->total->toDecimalString(),
                    'count' => $total->count,
                ],
                $summary->byCategory,
            ),
            'top_expenses' => array_map(
                fn (FinancialMovement $movement): array => MovementDTO::fromEntity(
                    $movement,
                    $movement->createdBy !== null ? $creatorNames[$movement->createdBy] ?? null : null,
                )->toArray(),
                $summary->topExpenses,
            ),
            'movements_count' => $summary->movementsCount,
        ];
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
}
