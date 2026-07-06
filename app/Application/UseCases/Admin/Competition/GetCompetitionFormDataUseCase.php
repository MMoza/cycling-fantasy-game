<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Competition;

use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CountryModel;

class GetCompetitionFormDataUseCase
{
    public function execute(?string $id = null): array
    {
        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        $competition = null;

        if ($id) {
            $model = CompetitionModel::findOrFail($id);
            $competition = [
                'id' => $model->id,
                'name' => $model->name,
                'type' => $model->type->value,
                'country_id' => $model->country_id,
                'active' => $model->active,
                'cover_image' => $model->cover_image,
                'logo_image' => $model->logo_image,
                'pcs_slug' => $model->pcs_slug,
            ];
        }

        return [
            'competition' => $competition,
            'countries' => $countries,
        ];
    }
}
