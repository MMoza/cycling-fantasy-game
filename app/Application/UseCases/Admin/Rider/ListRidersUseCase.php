<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Rider;

use App\Application\DTOs\Admin\RiderDTO;
use App\Infrastructure\Persistence\Models\CountryModel;
use App\Infrastructure\Persistence\Models\RiderModel;

class ListRidersUseCase
{
    public function execute(): array
    {
        $riders = RiderModel::with('country')
            ->leftJoin('competition_participants', function ($join) {
                $join->on('riders.id', '=', 'competition_participants.rider_id')
                    ->whereRaw('competition_participants.created_at = (
                        SELECT MAX(cp.created_at) FROM competition_participants cp
                        WHERE cp.rider_id = riders.id
                    )');
            })
            ->leftJoin('teams', 'competition_participants.team_id', '=', 'teams.id')
            ->orderBy('riders.last_name')
            ->orderBy('riders.first_name')
            ->select('riders.*', 'teams.name as team_name')
            ->get()
            ->map(fn ($r) => new RiderDTO(
                id: $r->id,
                firstName: $r->first_name,
                lastName: $r->last_name,
                fullName: $r->full_name,
                countryId: $r->country_id,
                profileImage: $r->profile_image,
                age: $r->age,
                teamName: $r->team_name,
            ));

        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        return [
            'riders' => $riders,
            'countries' => $countries,
        ];
    }
}
