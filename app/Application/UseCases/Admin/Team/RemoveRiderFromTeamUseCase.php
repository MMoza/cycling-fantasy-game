<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Team;

use App\Infrastructure\Persistence\Models\TeamModel;

class RemoveRiderFromTeamUseCase
{
    public function execute(string $teamId, string $riderId, int $year): void
    {
        $team = TeamModel::findOrFail($teamId);

        $team->rosters()
            ->where('rider_id', $riderId)
            ->where('year', $year)
            ->delete();
    }
}
