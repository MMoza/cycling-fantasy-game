<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin\Stage;

use App\Application\Exceptions\ApplicationException;
use App\Domain\Entities\Prediction;
use App\Domain\Entities\ScoringRule;
use App\Domain\Entities\ScoringSystem;
use App\Domain\Entities\StageResult as StageResultEntity;
use App\Domain\Services\ScoringEngine;
use App\Domain\ValueObjects\PredictionType;
use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreStageResultUseCase
{
    public function execute(string $editionId, string $id, array $results): void
    {
        $stage = StageModel::where('edition_id', $editionId)->findOrFail($id);

        if ($stage->status === StageStatus::Finished) {
            throw new ApplicationException('La etapa ya está finalizada');
        }

        $isRoadStage = $stage->type !== StageType::TimeTrial
            && $stage->type !== StageType::TeamTimeTrial
            && $stage->type !== StageType::Rest;

        $difficulty = $stage->difficulty ?? 1;

        $hasGcLeader = collect($results)->contains(fn ($r) => ($r['is_gc_leader'] ?? false) === true);
        $hasCombativo = collect($results)->contains(fn ($r) => ($r['is_combativo'] ?? false) === true);
        $count = count($results);

        $errors = [];

        if (! $hasGcLeader) {
            $errors[] = 'Debe marcar un corredor como líder de GC';
        }

        if ($isRoadStage && ! $hasCombativo) {
            $errors[] = 'Debe marcar un corredor como combativo del día';
        }

        if ($difficulty >= 3 && $count < 3) {
            $errors[] = 'Las etapas de 3 estrellas requieren al menos 3 posiciones (podio completo)';
        }

        if (! empty($errors)) {
            throw new ApplicationException(implode('. ', $errors));
        }

        DB::table('stage_results')->where('stage_id', $id)->delete();

        foreach ($results as $result) {
            DB::table('stage_results')->insert([
                'id' => Str::uuid()->toString(),
                'stage_id' => $id,
                'rider_id' => $result['rider_id'],
                'position' => $result['position'],
                'time' => $result['time'] ?? null,
                'gap' => $result['gap'] ?? null,
                'is_gc_leader' => $result['is_gc_leader'] ?? false,
                'is_combativo' => $result['is_combativo'] ?? false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $stage->update(['status' => StageStatus::Finished->value]);

        $this->scoreStage($stage);
    }

    private function scoreStage(StageModel $stage): void
    {
        $stageDifficulty = $stage->difficulty ?? 1;

        $resultsData = DB::table('stage_results')
            ->where('stage_id', $stage->id)
            ->orderBy('position')
            ->get();

        $stageResults = $resultsData->map(fn ($row) => StageResultEntity::fromRow($row));

        $leagues = DB::table('leagues')
            ->where('edition_id', $stage->edition_id)
            ->get();

        foreach ($leagues as $league) {
            $scoringSystemModel = ScoringSystemModel::with('rules')->find($league->scoring_system_id);

            if (! $scoringSystemModel) {
                continue;
            }

            $system = $this->buildScoringSystem($scoringSystemModel);
            $engine = new ScoringEngine($system);

            $predictions = PredictionModel::where('league_id', $league->id)
                ->where('stage_id', $stage->id)
                ->where('type', PredictionType::PreStage)
                ->get();

            foreach ($predictions as $predictionModel) {
                $prediction = Prediction::fromModel($predictionModel);

                foreach ($stageResults as $stageResult) {
                    $scoreEvent = $engine->calculateStageScore($prediction, $stageResult, $stageDifficulty, $stage->id);

                    if ($scoreEvent->points > 0) {
                        DB::table('score_events')->insert([
                            'id' => $scoreEvent->id,
                            'user_id' => $scoreEvent->userId,
                            'league_id' => $scoreEvent->leagueId,
                            'scoring_rule_id' => $scoreEvent->scoringRuleId,
                            'points' => $scoreEvent->points,
                            'description' => $scoreEvent->description,
                            'context' => $scoreEvent->context,
                            'stage_id' => $scoreEvent->stageId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
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
