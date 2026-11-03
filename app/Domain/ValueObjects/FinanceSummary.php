<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Entities\FinancialMovement;

/**
 * Resumen financiero confirmado de una temporada (año natural). Los
 * movimientos pendientes no participan en sus cálculos.
 */
readonly class FinanceSummary
{
    /**
     * @param  list<FinanceCategoryTotal>  $byCategory
     * @param  list<FinancialMovement>  $topExpenses
     */
    public function __construct(
        public int $year,
        public Money $incomeTotal,
        public Money $expenseTotal,
        public int $movementsCount,
        public array $byCategory,
        public array $topExpenses,
    ) {}

    public function balance(): Money
    {
        return $this->incomeTotal->subtract($this->expenseTotal);
    }
}
