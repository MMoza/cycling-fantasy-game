<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\FinancialMovement;
use App\Domain\Interfaces\FinancialMovementRepositoryInterface;
use App\Domain\ValueObjects\MovementFilters;
use App\Domain\ValueObjects\PaginatedResult;
use App\Infrastructure\Persistence\Models\FinancialMovementModel;

class EloquentFinancialMovementRepository implements FinancialMovementRepositoryInterface
{
    public function find(string $id): ?FinancialMovement
    {
        $model = FinancialMovementModel::find($id);

        return $model?->toEntity();
    }

    public function save(FinancialMovement $movement): void
    {
        FinancialMovementModel::updateOrCreate(
            ['id' => $movement->id],
            [
                'type' => $movement->type,
                'amount' => $movement->amount->toDecimalString(),
                'concept' => $movement->concept,
                'category' => $movement->category,
                'occurred_on' => $movement->occurredOn->format('Y-m-d'),
                'source' => $movement->source,
                'external_id' => $movement->externalId,
                'status' => $movement->status,
                'notes' => $movement->notes,
                'created_by' => $movement->createdBy,
            ],
        );
    }

    public function delete(string $id): void
    {
        FinancialMovementModel::where('id', $id)->delete();
    }

    public function search(MovementFilters $filters, int $perPage): PaginatedResult
    {
        $query = FinancialMovementModel::query()
            ->whereDate('occurred_on', '>=', $filters->periodStart())
            ->whereDate('occurred_on', '<=', $filters->periodEnd());

        if ($filters->type !== null) {
            $query->where('type', $filters->type->value);
        }

        if ($filters->category !== null) {
            $query->where('category', $filters->category->value);
        }

        if ($filters->from !== null) {
            $query->whereDate('occurred_on', '>=', $filters->from->format('Y-m-d'));
        }

        if ($filters->to !== null) {
            $query->whereDate('occurred_on', '<=', $filters->to->format('Y-m-d'));
        }

        $paginator = $query
            ->orderByDesc('occurred_on')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $filters->page);

        return new PaginatedResult(
            items: $paginator->getCollection()
                ->map(fn (FinancialMovementModel $model): FinancialMovement => $model->toEntity())
                ->all(),
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
        );
    }
}
