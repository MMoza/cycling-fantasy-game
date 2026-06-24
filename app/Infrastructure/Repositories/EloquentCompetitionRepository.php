<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Competition;
use App\Domain\Interfaces\CompetitionRepositoryInterface;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use Illuminate\Support\Collection;

class EloquentCompetitionRepository implements CompetitionRepositoryInterface
{
    public function find(string $id): ?Competition
    {
        $model = CompetitionModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findAll(): Collection
    {
        return CompetitionModel::all()->map(fn ($model) => $this->toEntity($model));
    }

    public function findByType(string $type): Collection
    {
        return CompetitionModel::where('type', $type)
            ->get()
            ->map(fn ($model) => $this->toEntity($model));
    }

    public function save(Competition $competition): void
    {
        CompetitionModel::updateOrCreate(
            ['id' => $competition->id],
            [
                'name' => $competition->name,
                'type' => $competition->type,
                'country_id' => $competition->country,
                'active' => $competition->active,
            ]
        );
    }

    public function delete(string $id): void
    {
        CompetitionModel::destroy($id);
    }

    private function toEntity(CompetitionModel $model): Competition
    {
        return new Competition(
            id: $model->id,
            name: $model->name,
            type: $model->type,
            country: $model->country_id,
            active: $model->active,
        );
    }
}
