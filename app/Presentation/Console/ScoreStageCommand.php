<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Application\Services\ActivityLogService;
use App\Domain\Entities\Prediction;
use App\Domain\Entities\ScoreEvent;
use App\Domain\Entities\ScoringRule;
use App\Domain\Entities\ScoringSystem;
use App\Domain\Entities\StageResult;
use App\Domain\Services\ScoringEngine;
use App\Domain\ValueObjects\ActivityLogType;
use App\Domain\ValueObjects\PredictionType;
use App\Domain\ValueObjects\StageStatus;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScoreStageCommand extends Command
{
    protected $signature = 'race:score-stage {stage_id : The stage UUID to score} {--force : Re-score even if already scored}';

    protected $description = 'Calculate scores for all predictions of a stage';

    public function handle(ActivityLogService $activityLog): int
    {
        $stageId = $this->argument('stage_id');

        $stage = StageModel::with('edition.leagues', 'edition.competition')->find($stageId);

        if (! $stage) {
            $this->error("Stage not found: {$stageId}");

            return self::FAILURE;
        }

        $stageDifficulty = $stage->difficulty ?? 1;

        $this->info("Scoring predictions for: {$stage->name} (difficulty: {$stageDifficulty})");

        $resultsData = DB::table('stage_results')
            ->where('stage_id', $stageId)
            ->orderBy('position')
            ->get();

        if ($resultsData->isEmpty()) {
            $this->error("No results found for stage {$stage->name}");

            return self::FAILURE;
        }

        $stageResults = $resultsData->map(fn ($row) => StageResult::fromRow($row));

        $riderTeamMap = DB::table('competition_participants')
            ->where('edition_id', $stage->edition_id)
            ->pluck('team_id', 'rider_id')
            ->toArray();

        $predictions = PredictionModel::with('league.scoringSystem.rules')
            ->where('stage_id', $stageId)
            ->where('type', PredictionType::PreStage)
            ->get();

        if ($predictions->isEmpty()) {
            $this->warn("No predictions found for stage {$stage->name}");

            return self::SUCCESS;
        }

        $this->info("Found {$predictions->count()} predictions to score");

        $scored = 0;

        foreach ($predictions->groupBy('league_id') as $leagueId => $leaguePredictions) {
            $alreadyScored = DB::table('score_events')
                ->where('league_id', $leagueId)
                ->where('stage_id', $stageId)
                ->exists();

            if ($alreadyScored) {
                if ($this->option('force')) {
                    DB::table('score_events')
                        ->where('league_id', $leagueId)
                        ->where('stage_id', $stageId)
                        ->delete();

                    $this->warn("Cleared existing score events for league {$leagueId}");
                } else {
                    $this->warn("League {$leagueId} already scored. Use --force to re-score.");

                    continue;
                }
            }

            $firstPrediction = $leaguePredictions->first();
            $scoringSystemModel = $firstPrediction->league->scoringSystem;

            if (! $scoringSystemModel) {
                $this->warn("No scoring system for league {$leagueId}");

                continue;
            }

            $scoringSystem = $this->buildScoringSystem($scoringSystemModel);

            $engine = new ScoringEngine($scoringSystem, $riderTeamMap);

            foreach ($leaguePredictions as $predictionModel) {
                $prediction = Prediction::fromModel($predictionModel);

                foreach ($stageResults as $stageResult) {
                    $scoreEvent = $engine->calculateStageScore($prediction, $stageResult, $stageDifficulty, $stageId);

                    if ($scoreEvent->points > 0) {
                        $this->persistScoreEvent($scoreEvent);
                        $scored++;
                    }
                }
            }

            $league = $stage->edition->leagues->firstWhere('id', $leagueId);
            if ($league && ! $activityLog->hasStageEndForLeague($league, $stageId)) {
                $activityLog->logStageEnd($league, $stage);
                $this->info("Logged stage_end for league {$leagueId}");
            }
        }

        $allStagesFinished = StageModel::where('edition_id', $stage->edition_id)
            ->where('type', '!=', 'rest')
            ->where('status', '!=', StageStatus::Finished)
            ->doesntExist();

        if ($allStagesFinished) {
            foreach ($stage->edition->leagues as $league) {
                if (! $activityLog->hasTypeForLeague($league, ActivityLogType::CompetitionEnd)) {
                    $activityLog->logCompetitionEnd($league);
                    $this->info("Logged competition_end for league {$league->id}");
                }
            }
        }

        $this->info("Scored {$scored} predictions for stage {$stage->name}");

        return self::SUCCESS;
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
}
