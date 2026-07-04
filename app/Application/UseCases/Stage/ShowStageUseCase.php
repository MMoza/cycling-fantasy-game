<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stage;

use App\Application\DTOs\PredictionDTO;
use App\Application\DTOs\StageDetailDTO;
use App\Domain\ValueObjects\StageStatus;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ShowStageUseCase
{
    public function execute(User $user, string $leagueId, string $stageId): array
    {
        $league = LeagueModel::with('edition')->findOrFail($leagueId);

        if (! $user->leagues()->where('leagues.id', $leagueId)->exists()) {
            abort(404);
        }

        $stage = StageModel::where('edition_id', $league->edition_id)
            ->findOrFail($stageId);

        $user->update(['last_visited_league_id' => $leagueId]);

        $isFinished = $stage->status === StageStatus::Finished;

        $predictions = PredictionModel::where('league_id', $leagueId)
            ->where('user_id', $user->id)
            ->where('stage_id', $stageId)
            ->where('type', 'pre_stage')
            ->get()
            ->keyBy(fn ($p) => $p->category->value)
            ->map(fn ($p) => new PredictionDTO(
                category: $p->category->value,
                value: $p->prediction_value,
                lockedAt: $p->locked_at?->toIso8601String(),
            ));

        $isLocked = $stage->status === StageStatus::Ongoing
            || ($stage->scheduled_start && now()->greaterThanOrEqualTo(
                $stage->scheduled_start->copy()->subMinutes(5)
            ));

        $prevStage = StageModel::where('edition_id', $league->edition_id)
            ->where('number', '<', $stage->number)
            ->orderBy('number', 'desc')
            ->first();

        $nextStage = StageModel::where('edition_id', $league->edition_id)
            ->where('number', '>', $stage->number)
            ->orderBy('number')
            ->first();

        $allStages = StageModel::where('edition_id', $league->edition_id)
            ->orderBy('number')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'number' => $s->number,
                'name' => $s->name,
            ]);

        $edition = $league->edition;

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

        $stageResults = [];
        $stageClassification = [];

        if ($isFinished) {
            $stageResults = DB::table('stage_results')
                ->join('riders', 'stage_results.rider_id', '=', 'riders.id')
                ->where('stage_results.stage_id', $stageId)
                ->orderBy('stage_results.position')
                ->select([
                    'stage_results.position',
                    'stage_results.rider_id',
                    'stage_results.time',
                    'stage_results.gap',
                    'stage_results.is_gc_leader',
                    'stage_results.is_combativo',
                    'riders.last_name',
                    'riders.first_name',
                    'riders.profile_image',
                ])
                ->get()
                ->map(fn ($r) => [
                    'position' => $r->position,
                    'rider_id' => $r->rider_id,
                    'rider_name' => trim("{$r->last_name} {$r->first_name}"),
                    'time' => $r->time,
                    'gap' => $r->gap,
                    'profile_image' => $r->profile_image,
                    'is_gc_leader' => (bool) $r->is_gc_leader,
                    'is_combativo' => (bool) $r->is_combativo,
                ]);

            $stageClassification = DB::table('score_events')
                ->join('users', 'score_events.user_id', '=', 'users.id')
                ->where('score_events.league_id', $leagueId)
                ->where('score_events.stage_id', $stageId)
                ->select([
                    'users.id as user_id',
                    'users.name as user_name',
                    DB::raw('SUM(score_events.points) as total_points'),
                ])
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total_points')
                ->get()
                ->map(fn ($r) => [
                    'user_id' => $r->user_id,
                    'user_name' => $r->user_name,
                    'total_points' => (int) $r->total_points,
                ]);
        }

        $stageDetail = new StageDetailDTO(
            id: $stage->id,
            number: $stage->number,
            name: $stage->name,
            date: $stage->date->format('d M'),
            type: $stage->type->label(),
            typeValue: $stage->type->value,
            distance: $stage->distance ? "{$stage->distance} km" : null,
            elevationGain: $stage->elevation_gain,
            profileImage: $stage->profile_image,
            origin: $stage->origin,
            destination: $stage->destination,
            difficulty: $stage->difficulty,
            status: $stage->status->value,
            scheduledStart: $stage->scheduled_start?->toIso8601String(),
        );

        return [
            'leagueId' => $league->id,
            'leagueName' => $league->name,
            'stage' => $stageDetail,
            'isFinished' => $isFinished,
            'isLocked' => $isLocked,
            'predictions' => $predictions,
            'stageResults' => $stageResults,
            'stageClassification' => $stageClassification,
            'navigation' => [
                'prev' => $prevStage ? ['id' => $prevStage->id, 'number' => $prevStage->number] : null,
                'next' => $nextStage ? ['id' => $nextStage->id, 'number' => $nextStage->number] : null,
            ],
            'allStages' => $allStages,
            'availableRiders' => $availableRiders,
            'availableTeams' => $availableTeams,
        ];
    }
}
