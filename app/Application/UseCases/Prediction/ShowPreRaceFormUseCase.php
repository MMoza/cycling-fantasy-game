<?php

declare(strict_types=1);

namespace App\Application\UseCases\Prediction;

use App\Domain\ValueObjects\PredictionType;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Models\User;

class ShowPreRaceFormUseCase
{
    public function execute(User $user, string $leagueId): array
    {
        $league = LeagueModel::with('edition.competition')->findOrFail($leagueId);

        if (! $user->leagues()->where('leagues.id', $leagueId)->exists()) {
            abort(404);
        }

        $user->update(['last_visited_league_id' => $leagueId]);

        $edition = $league->edition;

        $isLocked = $edition->status->value !== 'upcoming'
            || ($edition->start_date && now()->greaterThanOrEqualTo($edition->start_date));

        $predictions = PredictionModel::where('league_id', $leagueId)
            ->where('user_id', $user->id)
            ->whereNull('stage_id')
            ->where('type', PredictionType::PreRace)
            ->get()
            ->keyBy(fn ($p) => $p->category->value)
            ->map(fn ($p) => [
                'category' => $p->category->value,
                'value' => $p->prediction_value,
                'locked_at' => $p->locked_at?->toIso8601String(),
            ]);

        return [
            'leagueId' => $league->id,
            'leagueName' => $league->name,
            'competitionName' => $edition->competition->name,
            'competitionYear' => $edition->year,
            'isLocked' => $isLocked,
            'predictions' => $predictions,
        ];
    }
}
