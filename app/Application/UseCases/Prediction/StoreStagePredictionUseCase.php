<?php

declare(strict_types=1);

namespace App\Application\UseCases\Prediction;

use App\Application\Exceptions\ApplicationException;
use App\Domain\ValueObjects\PredictionType;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Models\User;
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

        foreach ($predictions as $prediction) {
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
                    'prediction_value' => ['rider_id' => $prediction['value']],
                ]
            );
        }
    }
}
