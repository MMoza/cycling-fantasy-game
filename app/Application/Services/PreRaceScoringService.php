<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Entities\Prediction;
use App\Domain\Entities\ScoreEvent;
use App\Domain\Entities\ScoringRule;
use App\Domain\Entities\ScoringSystem;
use App\Domain\Services\ScoringEngine;
use App\Domain\ValueObjects\ActivityLogType;
use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\ScoringRuleType;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\FinalClassificationModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PreRaceScoringService
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}

    public function scoreEdition(string $editionId, bool $force = false): array
    {
        $edition = EditionModel::findOrFail($editionId);

        $classifications = FinalClassificationModel::where('edition_id', $edition->id)->get();

        if ($classifications->isEmpty()) {
            return ['scored' => 0, 'leagues' => 0, 'skipped' => 0];
        }

        $grouped = $classifications->groupBy('category');

        $gcTop5 = $this->buildPositionMap($grouped->get('gc_top_5', collect()));
        $pointsPodium = $this->buildPositionMap($grouped->get('points_winner', collect()));
        $mountainsPodium = $this->buildPositionMap($grouped->get('mountains_winner', collect()));
        $youthPodium = $this->buildPositionMap($grouped->get('youth_winner', collect()));
        $teamsWinnerId = $grouped->get('teams_winner')?->first()?->team_id;
        $superCombativoId = $grouped->get('super_combativo')?->first()?->rider_id;

        $leagues = LeagueModel::with('edition.competition')
            ->where('edition_id', $edition->id)
            ->get();

        $totalScored = 0;
        $leaguesScored = 0;
        $leaguesSkipped = 0;

        foreach ($leagues as $league) {
            $alreadyScored = DB::table('score_events')
                ->where('league_id', $league->id)
                ->whereNull('stage_id')
                ->exists();

            if ($alreadyScored) {
                if ($force) {
                    DB::table('score_events')
                        ->where('league_id', $league->id)
                        ->whereNull('stage_id')
                        ->delete();
                } else {
                    $leaguesSkipped++;

                    continue;
                }
            }

            $scoringSystemModel = ScoringSystemModel::with('rules')
                ->find($league->scoring_system_id);

            if (! $scoringSystemModel) {
                $leaguesSkipped++;

                continue;
            }

            $scoringSystem = $this->buildScoringSystem($scoringSystemModel);
            $engine = new ScoringEngine($scoringSystem);

            $predictions = PredictionModel::where('league_id', $league->id)
                ->whereNull('stage_id')
                ->where('type', 'pre_race')
                ->get();

            if ($predictions->isEmpty()) {
                $leaguesSkipped++;

                continue;
            }

            foreach ($predictions as $predictionModel) {
                $prediction = Prediction::fromModel($predictionModel);

                $events = match ($prediction->category) {
                    PredictionCategory::GcTop5 => $gcTop5 ? $engine->calculateGcTop5Score($prediction, $gcTop5) : [],
                    PredictionCategory::PointsWinner => $pointsPodium ? $engine->calculateJerseyScore($prediction, $pointsPodium, ScoringRuleType::PointsWinner, ScoringRuleType::PointsWinnerPartial) : [],
                    PredictionCategory::MountainsWinner => $mountainsPodium ? $engine->calculateJerseyScore($prediction, $mountainsPodium, ScoringRuleType::MountainsWinner, ScoringRuleType::MountainsWinnerPartial) : [],
                    PredictionCategory::YouthWinner => $youthPodium ? $engine->calculateJerseyScore($prediction, $youthPodium, ScoringRuleType::YouthWinner, ScoringRuleType::YouthWinnerPartial) : [],
                    PredictionCategory::TeamsWinner => $teamsWinnerId ? [$engine->calculateSimpleScore($prediction, $teamsWinnerId, ScoringRuleType::TeamsWinner)] : [],
                    PredictionCategory::SuperCombativo => $superCombativoId ? [$engine->calculateSimpleScore($prediction, $superCombativoId, ScoringRuleType::SuperCombativo)] : [],
                    default => [],
                };

                foreach ($events as $event) {
                    if ($event->points > 0) {
                        $this->persistScoreEvent($event);
                        $totalScored++;
                    }
                }
            }

            if (! $this->activityLog->hasTypeForLeague($league, ActivityLogType::CompetitionStart)) {
                $this->activityLog->logCompetitionStart($league);
            }

            if (! $this->activityLog->hasTypeForLeague($league, ActivityLogType::CompetitionEnd)) {
                $this->activityLog->logCompetitionEnd($league);
            }

            if (! $this->activityLog->hasLeagueWinnerForLeague($league)) {
                $this->logLeagueWinner($league);
            }

            $leaguesScored++;
        }

        return [
            'scored' => $totalScored,
            'leagues' => $leaguesScored,
            'skipped' => $leaguesSkipped,
        ];
    }

    private function logLeagueWinner(LeagueModel $league): void
    {
        $winner = DB::table('score_events')
            ->where('league_id', $league->id)
            ->join('users', 'users.id', '=', 'score_events.user_id')
            ->selectRaw('users.id, users.name, users.avatar, SUM(score_events.points) as total_points')
            ->groupBy('users.id', 'users.name', 'users.avatar')
            ->orderByDesc('total_points')
            ->first();

        if (! $winner) {
            return;
        }

        $stagesWon = DB::table('predictions')
            ->join('stage_results', function ($join) {
                $join->on('predictions.stage_id', '=', 'stage_results.stage_id')
                    ->where('stage_results.position', '=', 1);
            })
            ->where('predictions.league_id', $league->id)
            ->where('predictions.user_id', $winner->id)
            ->where('predictions.category', 'stage_winner')
            ->whereRaw("JSON_EXTRACT(predictions.prediction_value, '$.rider_id') = stage_results.rider_id")
            ->count();

        $bestStage = DB::table('score_events')
            ->join('stages', 'stages.id', '=', 'score_events.stage_id')
            ->where('score_events.user_id', $winner->id)
            ->where('score_events.league_id', $league->id)
            ->whereNotNull('score_events.stage_id')
            ->selectRaw('stages.id, stages.number, stages.name, SUM(score_events.points) as stage_points')
            ->groupBy('stages.id', 'stages.number', 'stages.name')
            ->orderByDesc('stage_points')
            ->first();

        $this->activityLog->logLeagueWinner(
            league: $league,
            winnerName: $winner->name,
            winnerAvatar: $winner->avatar,
            winnerPoints: (int) $winner->total_points,
            stagesWon: $stagesWon,
            bestStage: $bestStage ? [
                'number' => $bestStage->number,
                'name' => $bestStage->name,
                'points' => (int) $bestStage->stage_points,
            ] : null,
        );
    }

    private function persistScoreEvent(ScoreEvent $event): void
    {
        DB::table('score_events')->insert([
            'id' => $event->id,
            'user_id' => $event->userId,
            'league_id' => $event->leagueId,
            'scoring_rule_id' => $event->scoringRuleId,
            'points' => $event->points,
            'description' => $event->description,
            'context' => $event->context,
            'stage_id' => $event->stageId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function buildPositionMap(Collection $items): array
    {
        if ($items->isEmpty()) {
            return [];
        }

        return $items
            ->sortBy('position')
            ->values()
            ->map(fn ($item) => $item->rider_id)
            ->toArray();
    }

    private function buildScoringSystem(ScoringSystemModel $model): ScoringSystem
    {
        $system = ScoringSystem::create(
            name: $model->name,
            type: $model->type,
            description: $model->description,
        );

        foreach ($model->rules as $rule) {
            $system = $system->addRule(
                ScoringRule::create(
                    scoringSystemId: $system->id,
                    type: $rule->type,
                    points: $rule->points,
                    difficulty: $rule->difficulty,
                    position: $rule->position,
                )
            );
        }

        return $system;
    }
}
