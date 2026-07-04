<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Stage;

use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Support\Facades\DB;

class ShowAdminStageUseCase
{
    public function execute(string $editionId, string $id): array
    {
        $edition = EditionModel::with('competition')->findOrFail($editionId);
        $stage = StageModel::where('edition_id', $editionId)->findOrFail($id);

        $isTTT = $stage->type === StageType::TeamTimeTrial;

        $ridersQuery = DB::table('competition_participants')
            ->join('riders', 'competition_participants.rider_id', '=', 'riders.id')
            ->join('teams', 'competition_participants.team_id', '=', 'teams.id')
            ->where('competition_participants.competition_id', $edition->competition_id)
            ->where('competition_participants.edition_id', $editionId)
            ->where('competition_participants.team_id', '!=', '')
            ->select('riders.id', 'riders.first_name', 'riders.last_name', 'riders.country_id', 'teams.name as team_name')
            ->distinct()
            ->orderBy('riders.last_name')
            ->orderBy('riders.first_name');

        $participantRiders = $ridersQuery->get()->map(fn ($r) => [
            'id' => $r->id,
            'name' => trim("{$r->last_name} {$r->first_name}"),
            'country_id' => $r->country_id,
        ]);

        $availableTeams = DB::table('competition_participants')
            ->join('teams', 'competition_participants.team_id', '=', 'teams.id')
            ->where('competition_participants.competition_id', $edition->competition_id)
            ->where('competition_participants.edition_id', $editionId)
            ->where('competition_participants.team_id', '!=', '')
            ->select('teams.id', 'teams.name')
            ->distinct()
            ->orderBy('teams.name')
            ->get()
            ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name]);

        $results = DB::table('stage_results')
            ->where('stage_id', $id)
            ->orderBy('position')
            ->get();

        return [
            'edition' => [
                'id' => $edition->id,
                'year' => $edition->year,
                'competition' => $edition->competition->name,
            ],
            'stage' => [
                'id' => $stage->id,
                'number' => $stage->number,
                'name' => $stage->name,
                'type' => $stage->type->label(),
                'type_value' => $stage->type->value,
                'date' => $stage->date->format('Y-m-d'),
                'distance' => $stage->distance,
                'elevation_gain' => $stage->elevation_gain,
                'difficulty' => $stage->difficulty,
                'origin' => $stage->origin,
                'destination' => $stage->destination,
                'status' => $stage->status->value,
                'status_label' => $stage->status->label(),
            ],
            'availableRiders' => $participantRiders,
            'availableTeams' => $availableTeams,
            'is_ttt' => $isTTT,
            'results' => $results->map(fn ($r) => [
                'id' => $r->id,
                'rider_id' => $r->rider_id,
                'position' => $r->position,
                'time' => $r->time,
                'gap' => $r->gap,
                'is_gc_leader' => (bool) $r->is_gc_leader,
                'is_combativo' => (bool) $r->is_combativo,
            ]),
        ];
    }
}
