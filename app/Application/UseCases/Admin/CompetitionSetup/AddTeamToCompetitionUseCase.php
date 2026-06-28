<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\CompetitionSetup;

use App\Application\Exceptions\ApplicationException;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\TeamModel;

class AddTeamToCompetitionUseCase
{
    public function execute(string $competitionId, string $editionId, string $teamId): void
    {
        $competition = CompetitionModel::findOrFail($competitionId);
        $edition = EditionModel::where('id', $editionId)->where('competition_id', $competitionId)->firstOrFail();
        $team = TeamModel::findOrFail($teamId);

        $rosteredRiders = $team->rosters()
            ->where('year', $edition->year)
            ->with('rider')
            ->get();

        if ($rosteredRiders->isEmpty()) {
            throw new ApplicationException('Este equipo no tiene corredores en su plantilla para esta temporada.');
        }

        foreach ($rosteredRiders as $roster) {
            CompetitionParticipantModel::firstOrCreate([
                'competition_id' => $competitionId,
                'edition_id' => $editionId,
                'team_id' => $teamId,
                'rider_id' => $roster->rider_id,
            ]);
        }
    }
}
