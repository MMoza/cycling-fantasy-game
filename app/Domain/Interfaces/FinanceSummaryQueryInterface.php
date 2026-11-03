<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

use App\Domain\ValueObjects\FinanceSummary;

/**
 * Consulta informe de finanzas (CQRS-lite): encapsula los agregados SQL de
 * la capa de persistencia para que Application reciba céntimos enteros ya
 * convertidos en value objects.
 */
interface FinanceSummaryQueryInterface
{
    /**
     * Resumen de los movimientos confirmados de una temporada.
     */
    public function summarizeYear(int $year): FinanceSummary;
}
