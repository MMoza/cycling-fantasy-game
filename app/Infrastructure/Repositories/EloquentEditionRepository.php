<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Edition;
use App\Domain\Interfaces\EditionRepositoryInterface;
use App\Infrastructure\Persistence\Models\EditionModel;

class EloquentEditionRepository implements EditionRepositoryInterface
{
    public function find(string $id): ?Edition
    {
        $model = EditionModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByCompetition(string $competitionId): array
    {
        return EditionModel::where('competition_id', $competitionId)
            ->orderBy('year', 'desc')
            ->get()
            ->map(fn ($model) => $this->toEntity($model))
            ->values()
            ->all();
    }

    public function findByYear(int $year): array
    {
        return EditionModel::where('year', $year)
            ->get()
            ->map(fn ($model) => $this->toEntity($model))
            ->values()
            ->all();
    }

    public function save(Edition $edition): void
    {
        EditionModel::updateOrCreate(
            ['id' => $edition->id],
            [
                'competition_id' => $edition->competitionId,
                'year' => $edition->year,
                'start_date' => $edition->startDate,
                'end_date' => $edition->endDate,
                'status' => $edition->status,
            ]
        );
    }

    public function delete(string $id): void
    {
        EditionModel::destroy($id);
    }

    private function toEntity(EditionModel $model): Edition
    {
        return new Edition(
            id: $model->id,
            competitionId: $model->competition_id,
            year: $model->year,
            startDate: $model->start_date->format('Y-m-d'),
            endDate: $model->end_date->format('Y-m-d'),
            status: $model->status,
        );
    }
}
