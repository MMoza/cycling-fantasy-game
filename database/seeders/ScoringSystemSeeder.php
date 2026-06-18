<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Entities\ScoringRule;
use App\Domain\Entities\ScoringSystem;
use App\Domain\ValueObjects\ScoringRuleType;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Infrastructure\Persistence\Models\ScoringRuleModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use Illuminate\Database\Seeder;

class ScoringSystemSeeder extends Seeder
{
    public function run(): void
    {
        $this->createStandard();
        $this->createAggressive();
        $this->createConservative();
    }

    private function createStandard(): void
    {
        $system = ScoringSystem::create(
            name: 'Estándar',
            type: ScoringSystemType::Standard,
            description: 'Puntuación equilibrada',
        );

        $rules = [
            [ScoringRuleType::StageWinner, 50],
            [ScoringRuleType::StageSecond, 30],
            [ScoringRuleType::StageThird, 20],
            [ScoringRuleType::StageLeader, 10],
            [ScoringRuleType::StageCombativo, 15],
            [ScoringRuleType::GcTop5, 100],
            [ScoringRuleType::PointsWinner, 80],
            [ScoringRuleType::MountainsWinner, 80],
            [ScoringRuleType::YouthWinner, 80],
            [ScoringRuleType::TeamsWinner, 60],
            [ScoringRuleType::SuperCombativo, 50],
        ];

        foreach ($rules as [$type, $points]) {
            $system = $system->addRule(ScoringRule::create($system->id, $type, $points));
        }

        $this->persistSystem($system);
    }

    private function createAggressive(): void
    {
        $system = ScoringSystem::create(
            name: 'Agresivo',
            type: ScoringSystemType::Aggressive,
            description: 'Premia más al ganador, menos al resto',
        );

        $rules = [
            [ScoringRuleType::StageWinner, 100],
            [ScoringRuleType::StageSecond, 40],
            [ScoringRuleType::StageThird, 20],
            [ScoringRuleType::StageLeader, 15],
            [ScoringRuleType::StageCombativo, 10],
            [ScoringRuleType::GcTop5, 200],
            [ScoringRuleType::PointsWinner, 100],
            [ScoringRuleType::MountainsWinner, 100],
            [ScoringRuleType::YouthWinner, 100],
            [ScoringRuleType::TeamsWinner, 80],
            [ScoringRuleType::SuperCombativo, 60],
        ];

        foreach ($rules as [$type, $points]) {
            $system = $system->addRule(ScoringRule::create($system->id, $type, $points));
        }

        $this->persistSystem($system);
    }

    private function createConservative(): void
    {
        $system = ScoringSystem::create(
            name: 'Conservador',
            type: ScoringSystemType::Conservative,
            description: 'Puntuación más repartida',
        );

        $rules = [
            [ScoringRuleType::StageWinner, 30],
            [ScoringRuleType::StageSecond, 25],
            [ScoringRuleType::StageThird, 20],
            [ScoringRuleType::StageLeader, 15],
            [ScoringRuleType::StageCombativo, 15],
            [ScoringRuleType::GcTop5, 80],
            [ScoringRuleType::PointsWinner, 70],
            [ScoringRuleType::MountainsWinner, 70],
            [ScoringRuleType::YouthWinner, 70],
            [ScoringRuleType::TeamsWinner, 60],
            [ScoringRuleType::SuperCombativo, 50],
        ];

        foreach ($rules as [$type, $points]) {
            $system = $system->addRule(ScoringRule::create($system->id, $type, $points));
        }

        $this->persistSystem($system);
    }

    private function persistSystem(ScoringSystem $system): void
    {
        ScoringSystemModel::create([
            'id' => $system->id,
            'name' => $system->name,
            'type' => $system->type,
            'description' => $system->description,
        ]);

        foreach ($system->rules as $rule) {
            ScoringRuleModel::create([
                'id' => $rule->id,
                'scoring_system_id' => $rule->scoringSystemId,
                'type' => $rule->type,
                'context' => $rule->context,
                'points' => $rule->points,
            ]);
        }
    }
}
