<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Domain\Entities\Prediction;
use App\Domain\Entities\ScoreEvent;
use App\Domain\Entities\ScoringRule;
use App\Domain\Entities\ScoringSystem;
use App\Domain\Entities\StageResult;
use App\Domain\Services\ScoringEngine;
use App\Domain\ValueObjects\PredictionType;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScoreStageCommand extends Command
{
    protected $signature = 'race:score-stage {stage_id : The stage UUID to score} {--force : Re-score even if already scored}';

    protected $description = 'Calculate scores for all predictions of a stage';

    public function handle(): int
    {
        $stageId = $this->argument('stage_id');

        $stage = StageModel::with('edition.leagues')->find($stageId);

        if (! $stage) {
            $this->error("Stage not found: {$stageId}");

            return self::FAILURE;
        }

        $this->info("Scoring predictions for: {$stage->name}");

        $resultsData = DB::table('stage_results')
            ->where('stage_id', $stageId)
            ->orderBy('position')
            ->get();

        if ($resultsData->isEmpty()) {
            $this->error("No results found for stage {$stage->name}");

            return self::FAILURE;
        }

        $stageResults = $resultsData->map(fn ($row) => StageResult::fromRow($row));

        $predictions = PredictionModel::with('league.scoringSystem.rules')
            ->where('stage_id', $stageId)
            ->where('type', PredictionType::PreStage)
            ->get();

        if ($predictions->isEmpty()) {
            $this->warn("No predictions found for stage {$stage->name}");

            return self::SUCCESS;
        }

        $this->info("Found {$predictions->count()} predictions to score");

        $leagueIds = $predictions->pluck('league_id')->unique()->toArray();
        $userId = $predictions->first()->user_id;

        $alreadyScored = DB::table('score_events')
            ->whereIn('league_id', $leagueIds)
            ->where('user_id', $userId)
            ->where('context', 'like', "stage_%")
            ->exists();

        if ($alreadyScored && ! $this->option('force')) {
            $this->warn('Stage already scored. Use --force to re-score.');

            return self::SUCCESS;
        }

        if ($this->option('force')) {
            DB::table('score_events')
                ->whereIn('league_id', $leagueIds)
                ->where('context', 'like', "stage_%")
                ->delete();

            $this->info('Cleared existing score events for re-scoring');
        }

        $scored = 0;

        foreach ($predictions->groupBy('league_id') as $leagueId => $leaguePredictions) {
            $firstPrediction = $leaguePredictions->first();
            $scoringSystemModel = $firstPrediction->league->scoringSystem;

            if (! $scoringSystemModel) {
                $this->warn("No scoring system for league {$leagueId}");

                continue;
            }

            $scoringSystem = $this->buildScoringSystem($scoringSystemModel);

            $engine = new ScoringEngine($scoringSystem);

            foreach ($leaguePredictions as $predictionModel) {
                $prediction = Prediction::fromModel($predictionModel);

                foreach ($stageResults as $stageResult) {
                    $scoreEvent = $engine->calculateStageScore($prediction, $stageResult, $stageId);

                    if ($scoreEvent->points > 0) {
                        $this->persistScoreEvent($scoreEvent);
                        $scored++;
                    }
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
