<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Competition;

use App\Infrastructure\Persistence\Models\CompetitionModel;

class StoreCompetitionUseCase
{
    public function execute(array $data): CompetitionModel
    {
        return CompetitionModel::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'country_id' => $data['country_id'],
            'cover_image' => $data['cover_image'] ?? null,
            'logo_image' => $data['logo_image'] ?? null,
            'pcs_slug' => $data['pcs_slug'] ?? null,
        ]);
    }
}
