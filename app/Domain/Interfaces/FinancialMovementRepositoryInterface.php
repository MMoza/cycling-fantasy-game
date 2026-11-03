<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

use App\Domain\Entities\FinancialMovement;
use App\Domain\ValueObjects\MovementFilters;
use App\Domain\ValueObjects\PaginatedResult;

interface FinancialMovementRepositoryInterface
{
    public function find(string $id): ?FinancialMovement;

    public function save(FinancialMovement $movement): void;

    public function delete(string $id): void;

    /**
     * @return PaginatedResult<FinancialMovement>
     */
    public function search(MovementFilters $filters, int $perPage): PaginatedResult;
}
