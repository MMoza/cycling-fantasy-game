<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\CompetitionSetup;

use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use Illuminate\Support\Str;

class ToggleRiderUseCase
{
    public function execute(string $competitionId, string $editionId, string $teamId, string $riderId, bool $active): void
    {
        if ($active) {
            CompetitionParticipantModel::create([
                'id' => Str::uuid()->toString(),
                'competition_id' => $competitionId,
                'edition_id' => $editionId,
                'team_id' => $teamId,
                'rider_id' => $riderId,
            ]);
        } else {
            CompetitionParticipantModel::where('competition_id', $competitionId)
                ->where('edition_id', $editionId)
                ->where('team_id', $teamId)
                ->where('rider_id', $riderId)
                ->delete();
        }
    }
}
