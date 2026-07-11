<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Stage;

use App\Application\DTOs\Admin\AdminStageDTO;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\StageModel;

class ListAdminStagesUseCase
{
    public function execute(string $editionId): array
    {
        $edition = EditionModel::with('competition')->findOrFail($editionId);

        $stages = StageModel::where('edition_id', $editionId)
            ->orderBy('number')
            ->get()
            ->map(fn ($s) => new AdminStageDTO(
                id: $s->id,
                number: $s->number,
                name: $s->name,
                date: $s->date->format('Y-m-d'),
                type: $s->type->label(),
                typeValue: $s->type->value,
                distance: $s->distance,
                elevationGain: $s->elevation_gain,
                difficulty: $s->difficulty,
                origin: $s->origin,
                destination: $s->destination,
                status: $s->status->label(),
                profileImage: $s->profile_image,
                scheduledStart: $s->scheduled_start?->format('H:i'),
            ));

        return [
            'edition' => [
                'id' => $edition->id,
                'year' => $edition->year,
                'competition_id' => $edition->competition->id,
                'competition' => $edition->competition->name,
            ],
            'stages' => $stages,
        ];
    }
}
