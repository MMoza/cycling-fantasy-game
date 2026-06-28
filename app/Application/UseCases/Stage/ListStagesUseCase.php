<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stage;

use App\Application\DTOs\StageDTO;
use App\Domain\ValueObjects\PredictionType;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Models\User;

class ListStagesUseCase
{
    public function execute(User $user, string $leagueId): array
    {
        $league = LeagueModel::with('edition')->findOrFail($leagueId);

        if (! $user->leagues()->where('leagues.id', $leagueId)->exists()) {
            abort(404);
        }

        $user->update(['last_visited_league_id' => $leagueId]);

        $edition = $league->edition;

        $stages = StageModel::where('edition_id', $edition->id)
            ->orderBy('number')
            ->get()
            ->map(fn (StageModel $s) => new StageDTO(
                id: $s->id,
                number: $s->number,
                name: $s->name,
                date: $s->date->format('d M'),
                type: $s->type->label(),
                typeValue: $s->type->value,
                distance: $s->distance,
                origin: $s->origin,
                destination: $s->destination,
                status: $s->status->value,
                difficulty: $s->difficulty,
                profileImage: $s->profile_image,
                elevationGain: $s->elevation_gain,
                scheduledStart: $s->scheduled_start?->toIso8601String(),
            ));

        $stageIds = $stages->pluck('id');

        $predictionsPerStage = PredictionModel::where('league_id', $leagueId)
            ->where('user_id', $user->id)
            ->whereIn('stage_id', $stageIds)
            ->where('type', PredictionType::PreStage)
            ->get()
            ->groupBy('stage_id')
            ->map(fn ($preds) => true);

        $pointsPerStage = ScoreEventModel::where('league_id', $leagueId)
            ->where('user_id', $user->id)
            ->whereNotNull('stage_id')
            ->whereIn('stage_id', $stageIds)
            ->get()
            ->groupBy('stage_id')
            ->map(fn ($events) => $events->sum('points'));

        $stages = $stages->map(function (StageDTO $s) use ($predictionsPerStage, $pointsPerStage) {
            return new StageDTO(
                id: $s->id,
                number: $s->number,
                name: $s->name,
                date: $s->date,
                type: $s->type,
                typeValue: $s->typeValue,
                distance: $s->distance,
                origin: $s->origin,
                destination: $s->destination,
                status: $s->status,
                difficulty: $s->difficulty,
                profileImage: $s->profileImage,
                elevationGain: $s->elevationGain,
                scheduledStart: $s->scheduledStart,
                hasPredictions: $predictionsPerStage->has($s->id),
                points: $pointsPerStage->get($s->id, 0),
            );
        });

        return [
            'leagueId' => $league->id,
            'leagueName' => $league->name,
            'competitionName' => $edition->competition->name,
            'year' => $edition->year,
            'stages' => $stages,
        ];
    }
}
