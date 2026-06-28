<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\CompetitionSetup;

use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\TeamModel;

class ShowSetupUseCase
{
    public function execute(string $competitionId, string $editionId): array
    {
        $competition = CompetitionModel::findOrFail($competitionId);
        $edition = EditionModel::with('stages')->findOrFail($editionId);
        $year = $edition->year;

        $teamIds = CompetitionParticipantModel::where('competition_id', $competitionId)
            ->where('edition_id', $editionId)
            ->distinct()
            ->pluck('team_id');

        $activeRiderIds = CompetitionParticipantModel::where('competition_id', $competitionId)
            ->where('edition_id', $editionId)
            ->pluck('rider_id')
            ->toArray();

        $participants = TeamModel::whereIn('id', $teamIds)
            ->with(['rosters' => fn ($q) => $q->where('year', $year)->with('rider.country')])
            ->get()
            ->map(fn ($team) => [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'riders' => $team->rosters->map(fn ($r) => [
                    'id' => $r->rider->id,
                    'full_name' => $r->rider->full_name,
                    'country' => $r->rider->country?->name,
                    'active' => in_array($r->rider_id, $activeRiderIds),
                ]),
            ]);

        $allTeams = TeamModel::orderBy('name')->get(['id', 'name']);

        return [
            'competition' => ['id' => $competition->id, 'name' => $competition->name],
            'edition' => [
                'id' => $edition->id,
                'year' => $edition->year,
                'stages_count' => $edition->stages->count(),
            ],
            'participants' => $participants,
            'teams' => $allTeams,
        ];
    }
}
