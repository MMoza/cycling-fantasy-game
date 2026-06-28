<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Edition;

use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;

class GetEditionFormDataUseCase
{
    public function execute(string $competitionId, ?string $id = null): array
    {
        $competition = CompetitionModel::findOrFail($competitionId);

        $edition = null;

        if ($id) {
            $model = EditionModel::where('competition_id', $competitionId)->findOrFail($id);
            $edition = [
                'id' => $model->id,
                'year' => $model->year,
                'start_date' => $model->start_date->format('Y-m-d'),
                'end_date' => $model->end_date->format('Y-m-d'),
                'status' => $model->status->value,
            ];
        }

        return [
            'competition' => ['id' => $competition->id, 'name' => $competition->name],
            'edition' => $edition,
        ];
    }
}
