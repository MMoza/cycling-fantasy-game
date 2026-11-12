<?php

declare(strict_types=1);

namespace App\Infrastructure\Queries;

use App\Domain\Entities\FinancialMovement;
use App\Domain\Interfaces\FinanceSummaryQueryInterface;
use App\Domain\ValueObjects\FinanceCategoryTotal;
use App\Domain\ValueObjects\FinanceSummary;
use App\Domain\ValueObjects\Money;
use App\Domain\ValueObjects\MovementCategory;
use App\Domain\ValueObjects\MovementStatus;
use App\Domain\ValueObjects\MovementType;
use App\Infrastructure\Persistence\Models\FinancialMovementModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Agregados monetarios en céntimos enteros mediante SQL portable
 * (SUM(ROUND(amount * 100)), válido en MySQL y SQLite). El SQL informe no
 * sale de esta capa.
 */
class EloquentFinanceSummaryQuery implements FinanceSummaryQueryInterface
{
    private const TOP_EXPENSES_LIMIT = 5;

    public function summarizeYear(int $year): FinanceSummary
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

        return new FinanceSummary(
            year: $year,
            incomeTotal: $incomeTotal,
            expenseTotal: $expenseTotal,
            movementsCount: $movementsCount,
            byCategory: $this->totalsByCategory($year),
            topExpenses: $this->topExpenses($year),
        );
    }

    /**
     * @return list<FinanceCategoryTotal>
     */
    private function totalsByCategory(int $year): array
    {
        $totals = [];

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

            $totals[] = new FinanceCategoryTotal(
                type: $type,
                category: $category,
                total: Money::fromCentsAggregate($row->cents),
                count: (int) $row->movements,
            );
        }

        return $totals;
    }

    /**
     * @return list<FinancialMovement>
     */
    private function topExpenses(int $year): array
    {
        return $this->confirmedQuery($year)
            ->where('type', MovementType::Expense->value)
            ->orderByDesc('amount')
            ->orderBy('id')
            ->limit(self::TOP_EXPENSES_LIMIT)
            ->get()
            ->map(fn (FinancialMovementModel $model): FinancialMovement => $model->toEntity())
            ->all();
    }

    /**
     * @return Builder<FinancialMovementModel>
     */
    private function confirmedQuery(int $year): Builder
    {
        return FinancialMovementModel::query()
            ->where('status', MovementStatus::Confirmed->value)
            ->whereDate('occurred_on', '>=', sprintf('%04d-01-01', $year))
            ->whereDate('occurred_on', '<=', sprintf('%04d-12-31', $year));
    }
}
