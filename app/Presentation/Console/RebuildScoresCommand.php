<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Domain\Entities\Prediction;
use App\Domain\Entities\ScoringRule;
use App\Domain\Entities\ScoringSystem;
use App\Domain\Entities\StageResult;
use App\Domain\Services\ScoringEngine;
use App\Domain\ValueObjects\PredictionType;
use App\Domain\ValueObjects\StageStatus;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildScoresCommand extends Command
{
    protected $signature = 'race:rebuild-scores {league_id? : The league UUID to rebuild (omit for all)}';

    protected $description = 'Delete and recalculate all scores from ScoreEvents for a league or all leagues';

    public function handle(): int
    {
        $leagueId = $this->argument('league_id');

        $leaguesQuery = LeagueModel::query();

        if ($leagueId) {
            $leaguesQuery->where('id', $leagueId);
        }

        $leagues = $leaguesQuery->with('edition')->get();

        if ($leagues->isEmpty()) {
            $this->warn('No leagues found');

            return self::SUCCESS;
        }

        $totalScored = 0;

        foreach ($leagues as $league) {
            $this->info("Rebuilding scores for league: {$league->name}");

            DB::table('score_events')->where('league_id', $league->id)->delete();

            $scoringSystemModel = ScoringSystemModel::with('rules')
                ->find($league->scoring_system_id);

            if (! $scoringSystemModel) {
                $this->warn("No scoring system for league {$league->id}");

                continue;
            }

            $scoringSystem = $this->buildScoringSystem($scoringSystemModel);
            $engine = new ScoringEngine($scoringSystem);

            $finishedStages = StageModel::where('edition_id', $league->edition_id)
                ->where('status', StageStatus::Finished)
                ->orderBy('number')
                ->get();

            if ($finishedStages->isEmpty()) {
                $this->info("No finished stages for league {$league->name}");

                continue;
            }

            foreach ($finishedStages as $stage) {
                $resultsData = DB::table('stage_results')
                    ->where('stage_id', $stage->id)
                    ->orderBy('position')
                    ->get();

                if ($resultsData->isEmpty()) {
                    continue;
                }

                $stageResults = $resultsData->map(fn ($row) => StageResult::fromRow($row));

                $predictions = PredictionModel::where('league_id', $league->id)
                    ->where('stage_id', $stage->id)
                    ->where('type', PredictionType::PreStage)
                    ->get();

                foreach ($predictions as $predictionModel) {
                    $prediction = Prediction::fromModel($predictionModel);

                    foreach ($stageResults as $stageResult) {
                        $scoreEvent = $engine->calculateStageScore($prediction, $stageResult);

                        if ($scoreEvent->points > 0) {
                            DB::table('score_events')->insert([
                                'id' => $scoreEvent->id,
                                'user_id' => $scoreEvent->userId,
                                'league_id' => $scoreEvent->leagueId,
                                'scoring_rule_id' => $scoreEvent->scoringRuleId,
                                'points' => $scoreEvent->points,
                                'description' => $scoreEvent->description,
                                'context' => $scoreEvent->context,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            $totalScored++;
                        }
                    }
                }
            }
        }

        $this->info("Rebuild complete. Total score events created: {$totalScored}");

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
}
