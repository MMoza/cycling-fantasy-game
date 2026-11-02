<?php

declare(strict_types=1);

namespace App\Application\UseCases\Prediction;

use App\Application\Exceptions\ApplicationException;
use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\PredictionType;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\StageParticipantModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreStagePredictionUseCase
{
    /** Categorías que pueden repetir corredor respecto al resto de posiciones. */
    private const DUPLICABLE_CATEGORIES = ['stage_leader', 'stage_combativo'];

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

        $this->validateNoDuplicates($predictions, $isTTT);

        foreach ($predictions as $prediction) {
            $isTeamPick = $isTTT && $prediction['category'] !== 'stage_leader';

            if ($isTeamPick) {
                $this->validateTeam($prediction['value'], $stage->edition_id, $stage->id);
            } else {
                $this->validateRider($prediction['value'], $stage->edition_id, $stage->id);
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

    /**
     * Impide guardar el mismo corredor (o equipo) en varias posiciones.
     *
     * `stage_leader` y `stage_combativo` pueden repetir respecto al resto.
     *
     * @param  array<int, mixed>  $predictions
     */
    private function validateNoDuplicates(array $predictions, bool $isTTT): void
    {
        $categoriesByValue = [];

        foreach ($predictions as $prediction) {
            if (in_array($prediction['category'], self::DUPLICABLE_CATEGORIES, true)) {
                continue;
            }

            $categoriesByValue[$prediction['value']][] = $prediction['category'];
        }

        $duplicated = array_filter(
            $categoriesByValue,
            fn (array $categories) => count($categories) > 1,
        );

        if ($duplicated === []) {
            return;
        }

        $problems = [];

        foreach ($duplicated as $value => $categories) {
            $isTeamPick = $isTTT && $categories[0] !== 'stage_leader';

            $name = $isTeamPick
                ? TeamModel::find($value)?->name
                : RiderModel::find($value)?->full_name;

            $labels = implode(', ', array_map(
                fn (string $category) => PredictionCategory::from($category)->label(),
                $categories,
            ));

            $problems[] = sprintf('«%s» está seleccionado en varias posiciones (%s)', $name ?? $value, $labels);
        }

        throw new ApplicationException(sprintf(
            'No se puede guardar el pronóstico: %s. Corrige las posiciones antes de guardar.',
            implode('; ', $problems),
        ));
    }

    private function validateRider(string $riderId, string $editionId, string $stageId): void
    {
        $hasStageParticipants = StageParticipantModel::where('stage_id', $stageId)->exists();

        if ($hasStageParticipants) {
            $exists = StageParticipantModel::where('stage_id', $stageId)
                ->where('rider_id', $riderId)
                ->exists();
        } else {
            $exists = DB::table('competition_participants')
                ->where('edition_id', $editionId)
                ->where('rider_id', $riderId)
                ->exists();
        }

        if (! $exists) {
            throw new ApplicationException('El corredor seleccionado no participa en esta etapa');
        }
    }

    private function validateTeam(string $teamId, string $editionId, string $stageId): void
    {
        $hasStageParticipants = StageParticipantModel::where('stage_id', $stageId)->exists();

        if ($hasStageParticipants) {
            $exists = StageParticipantModel::where('stage_id', $stageId)
                ->where('team_id', $teamId)
                ->exists();
        } else {
            $exists = DB::table('competition_participants')
                ->where('edition_id', $editionId)
                ->where('team_id', $teamId)
                ->exists();
        }

        if (! $exists) {
            throw new ApplicationException('El equipo seleccionado no participa en esta etapa');
        }
    }
}
