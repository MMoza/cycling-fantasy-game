<?php

declare(strict_types=1);

namespace App\Application\UseCases\Prediction;

use App\Application\Exceptions\ApplicationException;
use App\Domain\ValueObjects\PredictionType;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Models\User;
use Illuminate\Support\Str;

class StorePreRacePredictionUseCase
{
    public function execute(User $user, string $leagueId, array $predictions): void
    {
        $league = LeagueModel::with('edition')->findOrFail($leagueId);

        if (! $user->leagues()->where('leagues.id', $leagueId)->exists()) {
            abort(404);
        }

        $edition = $league->edition;

        if ($edition->status->value !== 'upcoming'
            || ($edition->start_date && now()->greaterThanOrEqualTo($edition->start_date))) {
            throw new ApplicationException('La competición ya ha comenzado');
        }

        foreach ($predictions as $prediction) {
            $value = $prediction['value'];

            if ($prediction['category'] === 'gc_top_5' && is_string($value)) {
                $value = array_map('trim', explode(',', $value));
            } elseif (is_string($value)) {
                $value = ['rider_id' => $value];
            }

            PredictionModel::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'league_id' => $league->id,
                    'stage_id' => null,
                    'type' => PredictionType::PreRace,
                    'category' => $prediction['category'],
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'prediction_value' => $value,
                ]
            );
        }
    }
}
