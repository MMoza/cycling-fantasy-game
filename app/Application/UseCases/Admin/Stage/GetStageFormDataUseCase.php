<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Stage;

use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\StageModel;

class GetStageFormDataUseCase
{
    public function execute(string $editionId, ?string $id = null): array
    {
        $edition = EditionModel::with('competition')->findOrFail($editionId);

        $stage = null;

        if ($id) {
            $model = StageModel::where('edition_id', $editionId)->findOrFail($id);
            $stage = [
                'id' => $model->id,
                'number' => $model->number,
                'name' => $model->name,
                'date' => $model->date->format('Y-m-d'),
                'type' => $model->type->value,
                'distance' => $model->distance,
                'elevation_gain' => $model->elevation_gain,
                'difficulty' => $model->difficulty,
                'origin' => $model->origin,
                'destination' => $model->destination,
                'profile_image' => $model->profile_image,
                'scheduled_start' => $model->scheduled_start?->toIso8601String(),
                'live_stream_url' => $model->live_stream_url,
                'status' => $model->status->value,
            ];
        }

        $stageTypes = collect(StageType::cases())->map(fn ($t) => [
            'value' => $t->value,
            'label' => $t->label(),
        ]);

        return [
            'edition' => [
                'id' => $edition->id,
                'year' => $edition->year,
                'competition' => $edition->competition->name,
                'competition_id' => $edition->competition->id,
                'competition_type' => $edition->competition->type->value,
            ],
            'stage' => $stage,
            'stageTypes' => $stageTypes,
        ];
    }
}
