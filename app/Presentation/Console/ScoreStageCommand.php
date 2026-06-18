<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Domain\Entities\ScoreEvent;
use App\Domain\Entities\StageResult;
use App\Domain\Services\ScoringEngine;
use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\PredictionType;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Console\Command;

class ScoreStageCommand extends Command
{
    protected $signature = 'race:score-stage {stage_id : The stage UUID to score}';

    protected $description = 'Calculate scores for all predictions of a stage';

    public function handle(): int
    {
        $stageId = $this->argument('stage_id');

        $stage = StageModel::with(['edition.league'])->find($stageId);

        if (! $stage) {
            $this->error("Stage not found: {$stageId}");

            return self::FAILURE;
        }

        $this->info("Scoring predictions for: {$stage->name}");

        $stageResults = \Illuminate\Support\Facades\DB::table('stage_results')
            ->where('stage_id', $stageId)
            ->get();

        if ($stageResults->isEmpty()) {
            $this->error("No results found for stage {$stage->name}");

            return self::FAILURE;
        }

        $predictions = PredictionModel::where('stage_id', $stageId)
            ->where('type', PredictionType::PreStage)
            ->get();

        if ($predictions->isEmpty()) {
            $this->warn("No predictions found for stage {$stage->name}");

            return self::SUCCESS;
        }

        $this->info("Found {$predictions->count()} predictions to score");

        $scored = 0;

        foreach ($predictions as $prediction) {
            $scoringSystem = ScoringSystemModel::with('rules')
                ->find($prediction->league->scoring_system_id);

            if (! $scoringSystem) {
                $this->warn("No scoring system for league {$prediction->league_id}");

                continue;
            }

            $scoringSystemEntity = \App\Domain\Entities\ScoringSystem::create(
                name: $scoringSystem->name,
                type: $scoringSystem->type,
                description: $scoringSystem->description,
            );

            foreach ($scoringSystem->rules as $rule) {
                $scoringSystemEntity = $scoringSystemEntity->addRule(
                    \App\Domain\Entities\ScoringRule::create(
                        scoringSystemId: $scoringSystem->id,
                        type: $rule->type,
                        points: $rule->points,
                    )
                );
            }

            $engine = new ScoringEngine($scoringSystemEntity);

            $category = $prediction->category;

            foreach ($stageResults as $result) {
                $stageResult = new StageResult(
                    id: $result->id,
                    stageId: $result->stage_id,
                    riderId: $result->rider_id,
                    position: $result->position,
                    time: $result->time,
                    gap: $result->gap,
                );

                $scoreEvent = $engine->calculateStageScore($prediction, $stageResult);

                if ($scoreEvent->points > 0) {
                    \Illuminate\Support\Facades\DB::table('score_events')->insert([
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

                    $scored++;
                }
            }
        }

        $this->info("Scored {$scored} predictions for stage {$stage->name}");

        return self::SUCCESS;
    }
}
