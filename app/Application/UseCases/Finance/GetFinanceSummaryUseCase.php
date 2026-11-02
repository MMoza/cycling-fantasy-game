<?php

declare(strict_types=1);

namespace App\Application\UseCases\Finance;

use App\Application\DTOs\Finance\MovementDTO;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementStatus;
use App\Domain\ValueObjects\MovementType;
use App\Infrastructure\Persistence\Models\FinancialMovementModel;
use Illuminate\Database\Eloquent\Builder;

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
    private const TOP_EXPENSES_LIMIT = 5;

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
        $incomeTotal = Money::fromCents(0);
        $expenseTotal = Money::fromCents(0);
        $movementsCount = 0;

        foreach ($this->confirmedQuery($year)
            ->selectRaw('type, SUM(ROUND(amount * 100)) AS cents, COUNT(*) AS movements')
            ->groupBy('type')
            ->get() as $row) {
            $total = Money::fromCentsAggregate($row->cents);
            $movementsCount += (int) $row->movements;

            if ($row->type === MovementType::Income) {
                $incomeTotal = $total;
            } else {
                $expenseTotal = $total;
            }
        }

        return [
            'year' => $year,
            'period' => [
                'from' => $this->periodStart($year),
                'to' => $this->periodEnd($year),
            ],
            'income_total' => $incomeTotal->toDecimalString(),
            'expense_total' => $expenseTotal->toDecimalString(),
            'balance' => $incomeTotal->subtract($expenseTotal)->toDecimalString(),
            'by_category' => $this->totalsByCategory($year),
            'top_expenses' => $this->topExpenses($year),
            'movements_count' => $movementsCount,
        ];
    }

    /**
     * @return list<array{type: string, category: string, label: string, total: string, count: int}>
     */
    private function totalsByCategory(int $year): array
    {
        $byCategory = [];

        foreach ($this->confirmedQuery($year)
            ->selectRaw('type, category, SUM(ROUND(amount * 100)) AS cents, COUNT(*) AS movements')
            ->groupBy('type', 'category')
            ->orderBy('type')
            ->orderByDesc('cents')
            ->get() as $row) {
            /** @var MovementType $type */
            $type = $row->type;
            /** @var MovementCategory $category */
            $category = $row->category;

            $byCategory[] = [
                'type' => $type->value,
                'category' => $category->value,
                'label' => $category->label($type),
                'total' => Money::fromCentsAggregate($row->cents)->toDecimalString(),
                'count' => (int) $row->movements,
            ];
        }

        return $byCategory;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function topExpenses(int $year): array
    {
        return $this->confirmedQuery($year)
            ->where('type', MovementType::Expense->value)
            ->with('creator')
            ->orderByDesc('amount')
            ->orderBy('id')
            ->limit(self::TOP_EXPENSES_LIMIT)
            ->get()
            ->map(fn (FinancialMovementModel $movement): array => MovementDTO::fromModel($movement)->toArray())
            ->all();
    }

    /**
     * @return Builder<FinancialMovementModel>
     */
    private function confirmedQuery(int $year): Builder
    {
        return FinancialMovementModel::query()
            ->where('status', MovementStatus::Confirmed->value)
            ->whereDate('occurred_on', '>=', $this->periodStart($year))
            ->whereDate('occurred_on', '<=', $this->periodEnd($year));
    }

    private function periodStart(int $year): string
    {
        return sprintf('%04d-01-01', $year);
    }

    private function periodEnd(int $year): string
    {
        return sprintf('%04d-12-31', $year);
    }
}
