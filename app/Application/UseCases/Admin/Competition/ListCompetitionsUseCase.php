<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Competition;

use App\Application\DTOs\Admin\CompetitionDTO;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CountryModel;

class ListCompetitionsUseCase
{
    public function execute(): array
    {
        $competitions = CompetitionModel::with('country')
            ->withCount('editions')
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => new CompetitionDTO(
                id: $c->id,
                name: $c->name,
                type: $c->type->label(),
                countryId: $c->country_id,
                active: $c->active,
                editionsCount: $c->editions_count,
            ));

        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        return [
            'competitions' => $competitions,
            'countries' => $countries,
        ];
    }
}
