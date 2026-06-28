<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Team;

use App\Infrastructure\Persistence\Models\CountryModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\TeamModel;

class ShowTeamUseCase
{
    public function execute(string $id): array
    {
        $team = TeamModel::with('rosters.rider', 'country')->findOrFail($id);

        $rostersByYear = $team->rosters
            ->groupBy('year')
            ->map(fn ($rosters, $year) => [
                'year' => (int) $year,
                'riders' => $rosters->map(fn ($r) => [
                    'id' => $r->rider->id,
                    'full_name' => $r->rider->full_name,
                    'country_id' => $r->rider->country_id,
                ]),
            ])
            ->values();

        $allRiders = RiderModel::orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'last_name', 'first_name'])
            ->map(fn ($r) => [
                'id' => $r->id,
                'full_name' => $r->full_name,
            ]);

        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        return [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'abbreviation' => $team->abbreviation,
                'country_id' => $team->country_id,
                'logo_url' => $team->logo_url,
            ],
            'rosters' => $rostersByYear,
            'allRiders' => $allRiders,
            'countries' => $countries,
        ];
    }
}
