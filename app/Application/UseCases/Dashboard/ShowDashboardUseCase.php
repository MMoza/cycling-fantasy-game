<?php

declare(strict_types=1);

namespace App\Application\UseCases\Dashboard;

use App\Domain\ValueObjects\StageStatus;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Models\User;

class ShowDashboardUseCase
{
    public function execute(User $user): array
    {
        $league = null;

        if ($user->last_visited_league_id) {
            $league = LeagueModel::with('edition.competition')->find($user->last_visited_league_id);

            if (! $league || ! $user->leagues()->where('leagues.id', $league->id)->exists()) {
                $league = null;
            }
        }

        if (! $league) {
            $league = $user->leagues()->with('edition.competition')->first();
        }

        if (! $league) {
            return [
                'league' => null,
                'stage' => null,
            ];
        }

        $stage = StageModel::where('edition_id', $league->edition_id)
            ->whereIn('status', [StageStatus::Ongoing, StageStatus::Upcoming])
            ->orderByRaw("CASE WHEN status = 'ongoing' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_start')
            ->first();

        $stageData = $stage ? [
            'id' => $stage->id,
            'number' => $stage->number,
            'name' => $stage->name,
            'type' => $stage->type->label(),
            'type_value' => $stage->type->value,
            'origin' => $stage->origin,
            'destination' => $stage->destination,
            'distance' => $stage->distance,
            'difficulty' => $stage->difficulty,
            'scheduled_start' => $stage->scheduled_start?->toIso8601String(),
            'status' => $stage->status->value,
            'is_ongoing' => $stage->status === StageStatus::Ongoing,
        ] : null;

        return [
            'league' => [
                'id' => $league->id,
                'name' => $league->name,
                'competition' => $league->edition->competition->name,
                'year' => $league->edition->year,
            ],
            'stage' => $stageData,
        ];
    }
}
