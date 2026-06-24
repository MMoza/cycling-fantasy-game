<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Domain\Entities\Prediction;
use App\Domain\Entities\ScoringRule;
use App\Domain\Entities\ScoringSystem;
use App\Domain\Services\ScoringEngine;
use App\Domain\ValueObjects\PredictionType;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScorePreRaceCommand extends Command
{
    protected $signature = 'race:score-pre-race {edition_id? : The edition UUID to score (omit for all finished editions)}';

    protected $description = 'Calculate scores for all pre-race predictions of an edition';

    public function handle(): int
    {
        $editionId = $this->argument('edition_id');

        if ($editionId) {
            $editions = EditionModel::where('id', $editionId)->get();
        } else {
            $editions = EditionModel::where('status', 'finished')->get();
        }

        if ($editions->isEmpty()) {
            $this->warn('No editions found to score');

            return self::SUCCESS;
        }

        $scored = 0;

        foreach ($editions as $edition) {
            $this->info("Scoring pre-race predictions for edition: {$edition->year}");

            $leagues = DB::table('leagues')
                ->where('edition_id', $edition->id)
                ->get();

            foreach ($leagues as $league) {
                $scoringSystemModel = ScoringSystemModel::with('rules')
                    ->find($league->scoring_system_id);

                if (! $scoringSystemModel) {
                    continue;
                }

                $scoringSystem = $this->buildScoringSystem($scoringSystemModel);
                $engine = new ScoringEngine($scoringSystem);

                $predictions = PredictionModel::where('league_id', $league->id)
                    ->whereNull('stage_id')
                    ->where('type', PredictionType::PreRace)
                    ->get();

                if ($predictions->isEmpty()) {
                    continue;
                }

                foreach ($predictions as $predictionModel) {
                    $prediction = Prediction::fromModel($predictionModel);

                    $this->warn("Pre-race scoring for category {$prediction->category->value} requires GC results input — manual scoring placeholders for now");

                    $scored++;
                }
            }
        }

        $this->info("Processed {$scored} pre-race predictions");

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
