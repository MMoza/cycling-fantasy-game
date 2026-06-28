<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Rider;

use App\Infrastructure\Persistence\Models\CountryModel;
use App\Infrastructure\Persistence\Models\RiderModel;

class GetRiderFormDataUseCase
{
    public function execute(?string $id = null): array
    {
        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        $rider = null;

        if ($id) {
            $model = RiderModel::findOrFail($id);
            $rider = [
                'id' => $model->id,
                'first_name' => $model->first_name,
                'last_name' => $model->last_name,
                'country_id' => $model->country_id,
                'birth_date' => $model->birth_date?->format('Y-m-d'),
                'profile_image' => $model->profile_image,
            ];
        }

        return [
            'rider' => $rider,
            'countries' => $countries,
        ];
    }
}
