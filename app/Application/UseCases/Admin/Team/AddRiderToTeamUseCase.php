<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Team;

use App\Application\Exceptions\ApplicationException;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Infrastructure\Persistence\Models\TeamRosterModel;

class AddRiderToTeamUseCase
{
    public function execute(string $teamId, string $riderId, int $year): void
    {
        $team = TeamModel::findOrFail($teamId);

        $alreadyRostered = TeamRosterModel::where('rider_id', $riderId)
            ->where('year', $year)
            ->where('team_id', '!=', $teamId)
            ->exists();

        if ($alreadyRostered) {
            throw new ApplicationException('Este corredor ya pertenece a otro equipo en esta temporada.');
        }

        $team->rosters()->firstOrCreate([
            'rider_id' => $riderId,
            'year' => $year,
        ]);
    }
}
