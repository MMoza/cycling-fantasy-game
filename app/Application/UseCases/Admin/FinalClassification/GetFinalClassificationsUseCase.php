<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\FinalClassification;

use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\FinalClassificationModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\TeamModel;

class GetFinalClassificationsUseCase
{
    public function execute(string $editionId): array
    {
        $edition = EditionModel::with('competition')->findOrFail($editionId);

        $riders = RiderModel::orderBy('last_name')->orderBy('first_name')
            ->get()
            ->map(fn ($r) => ['value' => $r->id, 'label' => trim("{$r->last_name} {$r->first_name}")]);

        $teams = TeamModel::orderBy('name')
            ->get()
            ->map(fn ($t) => ['value' => $t->id, 'label' => $t->name]);

        $classifications = FinalClassificationModel::where('edition_id', $editionId)
            ->get()
            ->groupBy('category')
            ->map(fn ($items) => $items->keyBy('position'));

        return [
            'edition' => [
                'id' => $edition->id,
                'year' => $edition->year,
                'competition' => $edition->competition->name,
            ],
            'riders' => $riders,
            'teams' => $teams,
            'classifications' => $classifications,
        ];
    }
}
