<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Team;

use App\Infrastructure\Persistence\Models\CountryModel;
use App\Infrastructure\Persistence\Models\TeamModel;

class GetTeamFormDataUseCase
{
    public function execute(?string $id = null): array
    {
        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        $team = null;

        if ($id) {
            $model = TeamModel::findOrFail($id);
            $team = [
                'id' => $model->id,
                'name' => $model->name,
                'abbreviation' => $model->abbreviation,
                'country_id' => $model->country_id,
                'logo_url' => $model->logo_url,
            ];
        }

        return [
            'team' => $team,
            'countries' => $countries,
        ];
    }
}
