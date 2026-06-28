<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Team;

use App\Application\DTOs\Admin\TeamDTO;
use App\Infrastructure\Persistence\Models\CountryModel;
use App\Infrastructure\Persistence\Models\TeamModel;

class ListTeamsUseCase
{
    public function execute(): array
    {
        $teams = TeamModel::with('country')
            ->withCount('rosters')
            ->orderBy('name')
            ->get()
            ->map(fn ($t) => new TeamDTO(
                id: $t->id,
                name: $t->name,
                abbreviation: $t->abbreviation,
                countryId: $t->country_id,
                logoUrl: $t->logo_url,
                ridersCount: $t->rosters_count,
            ));

        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        return [
            'teams' => $teams,
            'countries' => $countries,
        ];
    }
}
