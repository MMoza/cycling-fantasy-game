<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Stage;

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\StageParticipantModel;
use Illuminate\Support\Facades\DB;

class GetStageFormDataUseCase
{
    public function execute(string $editionId, ?string $id = null): array
    {
        $edition = EditionModel::with('competition')->findOrFail($editionId);

        $stage = null;
        $stageParticipantIds = [];

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

            $stageParticipantIds = StageParticipantModel::where('stage_id', $id)
                ->pluck('rider_id')
                ->toArray();
        }

        $stageTypes = collect(StageType::cases())->map(fn ($t) => [
            'value' => $t->value,
            'label' => $t->label(),
        ]);

        $availableParticipants = [];

        if ($edition->competition->type === CompetitionType::Championship) {
            $availableParticipants = DB::table('competition_participants')
                ->join('riders', 'competition_participants.rider_id', '=', 'riders.id')
                ->join('teams', 'competition_participants.team_id', '=', 'teams.id')
                ->where('competition_participants.competition_id', $edition->competition_id)
                ->where('competition_participants.edition_id', $edition->id)
                ->select('riders.id', 'riders.first_name', 'riders.last_name', 'riders.country_id', 'teams.name as team_name')
                ->distinct()
                ->orderBy('riders.last_name')
                ->orderBy('riders.first_name')
                ->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'name' => trim("{$r->last_name} {$r->first_name}"),
                    'country_id' => $r->country_id,
                    'team_name' => $r->team_name,
                ])
                ->toArray();
        }

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
            'availableParticipants' => $availableParticipants,
            'stageParticipantIds' => $stageParticipantIds,
        ];
    }
}
