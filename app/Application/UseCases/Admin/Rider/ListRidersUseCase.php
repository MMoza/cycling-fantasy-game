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
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(fn ($r) => new RiderDTO(
                id: $r->id,
                firstName: $r->first_name,
                lastName: $r->last_name,
                fullName: $r->full_name,
                countryId: $r->country_id,
                profileImage: $r->profile_image,
                age: $r->age,
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
