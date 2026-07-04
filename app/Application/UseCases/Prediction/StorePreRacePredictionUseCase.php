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

        $firstStage = $edition->relationLoaded('stages')
            ? $edition->stages->first()
            : $edition->stages()->orderBy('date')->orderBy('scheduled_start')->first();

        $hasStarted = $firstStage
            ? ($firstStage->scheduled_start && now()->greaterThanOrEqualTo($firstStage->scheduled_start))
            : ($edition->start_date && now()->greaterThanOrEqualTo($edition->start_date));

        if ($edition->status->value !== 'upcoming' || $hasStarted) {
            throw new ApplicationException('La competición ya ha comenzado');
        }

        $user->update(['last_visited_league_id' => $leagueId]);

        foreach ($predictions as $prediction) {
            $rawValue = $prediction['value'] ?? '';
            $value = $rawValue;

            $multiValueCategories = ['gc_top_5', 'points_winner', 'mountains_winner', 'youth_winner'];

            if (in_array($prediction['category'], $multiValueCategories, true) && is_string($rawValue)) {
                $value = array_values(array_filter(array_map('trim', explode(',', $rawValue)), fn ($v) => $v !== ''));
                if (empty($value)) {
                    continue;
                }
            } elseif ($prediction['category'] === 'teams_winner' && is_string($rawValue)) {
                if ($rawValue === '') {
                    continue;
                }
                $value = ['team_id' => $rawValue];
            } elseif (is_string($rawValue)) {
                if ($rawValue === '') {
                    continue;
                }
                $value = ['rider_id' => $rawValue];
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
