<?php

declare(strict_types=1);

namespace App\Application\UseCases\Prediction;

use App\Application\Exceptions\ApplicationException;
use App\Domain\ValueObjects\PredictionType;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreStagePredictionUseCase
{
    public function execute(User $user, string $leagueId, string $stageId, array $predictions): void
    {
        $league = LeagueModel::findOrFail($leagueId);

        if (! $user->leagues()->where('leagues.id', $leagueId)->exists()) {
            abort(404);
        }

        $stage = StageModel::where('edition_id', $league->edition_id)
            ->findOrFail($stageId);

        if ($stage->scheduled_start && now()->greaterThanOrEqualTo($stage->scheduled_start)) {
            throw new ApplicationException('La etapa ya ha comenzado');
        }

        $isTTT = $stage->type === StageType::TeamTimeTrial;

        foreach ($predictions as $prediction) {
            $isTeamPick = $isTTT && $prediction['category'] !== 'stage_leader';

            if ($isTeamPick) {
                $this->validateTeam($prediction['value'], $stage->edition_id);
            } else {
                $this->validateRider($prediction['value'], $stage->edition_id);
            }

            PredictionModel::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'league_id' => $league->id,
                    'stage_id' => $stage->id,
                    'type' => PredictionType::PreStage,
                    'category' => $prediction['category'],
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'prediction_value' => [$isTeamPick ? 'team_id' : 'rider_id' => $prediction['value']],
                ]
            );
        }

        $user->update(['last_visited_league_id' => $leagueId]);
    }

    private function validateRider(string $riderId, string $editionId): void
    {
        $exists = DB::table('competition_participants')
            ->where('edition_id', $editionId)
            ->where('rider_id', $riderId)
            ->exists();

        if (! $exists) {
            throw new ApplicationException('El corredor seleccionado no participa en esta edición');
        }
    }

    private function validateTeam(string $teamId, string $editionId): void
    {
        $exists = DB::table('competition_participants')
            ->where('edition_id', $editionId)
            ->where('team_id', $teamId)
            ->exists();

        if (! $exists) {
            throw new ApplicationException('El equipo seleccionado no participa en esta edición');
        }
    }
}
