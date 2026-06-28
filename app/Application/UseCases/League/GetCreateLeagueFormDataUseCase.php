<?php

declare(strict_types=1);

namespace App\Application\UseCases\League;

use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;

class GetCreateLeagueFormDataUseCase
{
    public function execute(): array
    {
        $editions = EditionModel::with('competition')->get();
        $scoringSystems = ScoringSystemModel::all();

        return [
            'editions' => $editions->filter->competition->values()->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->competition->name,
                'year' => $e->year,
                'competition' => ['name' => $e->competition->name],
            ]),
            'scoringSystems' => $scoringSystems->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'description' => $s->description,
                'type' => $s->type->value,
            ]),
        ];
    }
}
