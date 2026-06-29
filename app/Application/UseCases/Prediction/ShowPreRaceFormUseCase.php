<?php

declare(strict_types=1);

namespace App\Application\UseCases\Prediction;

use App\Domain\ValueObjects\PredictionType;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

        $availableRiders = DB::table('competition_participants')
            ->join('riders', 'competition_participants.rider_id', '=', 'riders.id')
            ->where('competition_participants.competition_id', $edition->competition_id)
            ->where('competition_participants.edition_id', $edition->id)
            ->select('riders.id', 'riders.last_name', 'riders.first_name')
            ->distinct()
            ->orderBy('riders.last_name')
            ->orderBy('riders.first_name')
            ->get()
            ->map(fn ($r) => ['value' => $r->id, 'label' => trim("{$r->last_name} {$r->first_name}")]);

        $availableTeams = TeamModel::whereHas('rosters', fn ($q) => $q->where('year', $edition->year))
            ->orderBy('name')
            ->get()
            ->map(fn ($t) => ['value' => $t->id, 'label' => $t->name]);

        return [
            'leagueId' => $league->id,
            'leagueName' => $league->name,
            'competitionName' => $edition->competition->name,
            'competitionYear' => $edition->year,
            'isLocked' => $isLocked,
            'predictions' => $predictions,
            'availableRiders' => $availableRiders,
            'availableTeams' => $availableTeams,
        ];
    }
}
