<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\CompetitionSetup;

use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;

class RemoveTeamFromCompetitionUseCase
{
    public function execute(string $competitionId, string $editionId, string $teamId): void
    {
        CompetitionParticipantModel::where('competition_id', $competitionId)
            ->where('edition_id', $editionId)
            ->where('team_id', $teamId)
            ->delete();
    }
}
