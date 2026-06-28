<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Domain\Entities\Prediction;
use App\Domain\Entities\ScoringRule;
use App\Domain\Entities\ScoringSystem;
use App\Domain\Services\ScoringEngine;
use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\ScoringRuleType;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\FinalClassificationModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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

        $totalScored = 0;

        foreach ($editions as $edition) {
            $classifications = FinalClassificationModel::where('edition_id', $edition->id)->get();

            if ($classifications->isEmpty()) {
                $this->warn("No final classifications set for edition: {$edition->year}. Set them via admin panel first.");

                continue;
            }

            $grouped = $classifications->groupBy('category');

            $gcTop5 = $this->buildPositionMap($grouped->get('gc_top_5', collect()));
            $pointsPodium = $this->buildPositionMap($grouped->get('points_winner', collect()));
            $mountainsPodium = $this->buildPositionMap($grouped->get('mountains_winner', collect()));
            $youthPodium = $this->buildPositionMap($grouped->get('youth_winner', collect()));
            $teamsWinnerId = $grouped->get('teams_winner')?->first()?->team_id;
            $superCombativoId = $grouped->get('super_combativo')?->first()?->rider_id;

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
                    ->where('type', 'pre_race')
                    ->get();

                if ($predictions->isEmpty()) {
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

                            $totalScored++;
                        }
                    }
                }
            }
        }

        $this->info("Pre-race scoring complete. Total score events created: {$totalScored}");

        return self::SUCCESS;
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
