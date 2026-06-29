<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\ValueObjects\ScoringRuleType;
use App\Domain\ValueObjects\ScoringSystemType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScoringSystemSeeder extends Seeder
{
    public function run(): void
    {
        $this->createStandardSystem();
        $this->createAggressiveSystem();
        $this->createConservativeSystem();
    }

    private function createStandardSystem(): void
    {
        if (DB::table('scoring_systems')->where('type', ScoringSystemType::Standard->value)->exists()) {
            return;
        }

        $systemId = Str::uuid()->toString();

        DB::table('scoring_systems')->insert([
            'id' => $systemId,
            'name' => 'Estándar Tour',
            'type' => ScoringSystemType::Standard->value,
            'description' => 'Sistema de puntuación estándar para Grandes Vueltas. Puntuación por estrellas en etapas, clasificaciones finales y maillots.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->insertRules($systemId, [
            ['type' => ScoringRuleType::StageWinner, 'points' => 10, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 2, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageWinner, 'points' => 20, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 4, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageWinner, 'points' => 30, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 6, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageSecond, 'points' => 15, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageThird, 'points' => 10, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageLeader, 'points' => 5, 'difficulty' => null, 'position' => null],

            ['type' => ScoringRuleType::GcTop5, 'points' => 100, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::GcTop5, 'points' => 75, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::GcTop5, 'points' => 50, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::GcTop5, 'points' => 30, 'difficulty' => null, 'position' => 4],
            ['type' => ScoringRuleType::GcTop5, 'points' => 20, 'difficulty' => null, 'position' => 5],
            ['type' => ScoringRuleType::GcTop5Partial, 'points' => 15, 'difficulty' => null, 'position' => null],

            ['type' => ScoringRuleType::PointsWinner, 'points' => 40, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::PointsWinner, 'points' => 25, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::PointsWinner, 'points' => 15, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::PointsWinnerPartial, 'points' => 10, 'difficulty' => null, 'position' => null],

            ['type' => ScoringRuleType::MountainsWinner, 'points' => 40, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::MountainsWinner, 'points' => 25, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::MountainsWinner, 'points' => 15, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::MountainsWinnerPartial, 'points' => 10, 'difficulty' => null, 'position' => null],

            ['type' => ScoringRuleType::YouthWinner, 'points' => 40, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::YouthWinner, 'points' => 25, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::YouthWinner, 'points' => 15, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::YouthWinnerPartial, 'points' => 10, 'difficulty' => null, 'position' => null],

            ['type' => ScoringRuleType::TeamsWinner, 'points' => 30, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::SuperCombativo, 'points' => 30, 'difficulty' => null, 'position' => null],
        ]);
    }

    private function createAggressiveSystem(): void
    {
        if (DB::table('scoring_systems')->where('type', ScoringSystemType::Aggressive->value)->exists()) {
            return;
        }

        $systemId = Str::uuid()->toString();

        DB::table('scoring_systems')->insert([
            'id' => $systemId,
            'name' => 'Agresivo',
            'type' => ScoringSystemType::Aggressive->value,
            'description' => 'Premia más al ganador, menos al resto. Ideal para jugadores que buscan arriesgar y acertar el máximo número de ganadores.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->insertRules($systemId, [
            ['type' => ScoringRuleType::StageWinner, 'points' => 15, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 1, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageWinner, 'points' => 30, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 2, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageWinner, 'points' => 45, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 3, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageSecond, 'points' => 15, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageThird, 'points' => 10, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageLeader, 'points' => 3, 'difficulty' => null, 'position' => null],

            ['type' => ScoringRuleType::GcTop5, 'points' => 150, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::GcTop5, 'points' => 100, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::GcTop5, 'points' => 60, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::GcTop5, 'points' => 30, 'difficulty' => null, 'position' => 4],
            ['type' => ScoringRuleType::GcTop5, 'points' => 15, 'difficulty' => null, 'position' => 5],
            ['type' => ScoringRuleType::GcTop5Partial, 'points' => 10, 'difficulty' => null, 'position' => null],

            ['type' => ScoringRuleType::PointsWinner, 'points' => 60, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::PointsWinner, 'points' => 35, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::PointsWinner, 'points' => 20, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::PointsWinnerPartial, 'points' => 5, 'difficulty' => null, 'position' => null],

            ['type' => ScoringRuleType::MountainsWinner, 'points' => 60, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::MountainsWinner, 'points' => 35, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::MountainsWinner, 'points' => 20, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::MountainsWinnerPartial, 'points' => 5, 'difficulty' => null, 'position' => null],

            ['type' => ScoringRuleType::YouthWinner, 'points' => 60, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::YouthWinner, 'points' => 35, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::YouthWinner, 'points' => 20, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::YouthWinnerPartial, 'points' => 5, 'difficulty' => null, 'position' => null],

            ['type' => ScoringRuleType::TeamsWinner, 'points' => 20, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::SuperCombativo, 'points' => 20, 'difficulty' => null, 'position' => null],
        ]);
    }

    private function createConservativeSystem(): void
    {
        if (DB::table('scoring_systems')->where('type', ScoringSystemType::Conservative->value)->exists()) {
            return;
        }

        $systemId = Str::uuid()->toString();

        DB::table('scoring_systems')->insert([
            'id' => $systemId,
            'name' => 'Conservador',
            'type' => ScoringSystemType::Conservative->value,
            'description' => 'Puntuación más repartida entre posiciones. Premia la consistencia por encima de los aciertos exactos.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->insertRules($systemId, [
            ['type' => ScoringRuleType::StageWinner, 'points' => 8, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 3, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageWinner, 'points' => 16, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 5, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageWinner, 'points' => 25, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageSecond, 'points' => 18, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageThird, 'points' => 12, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 8, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageLeader, 'points' => 8, 'difficulty' => null, 'position' => null],

            ['type' => ScoringRuleType::GcTop5, 'points' => 80, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::GcTop5, 'points' => 65, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::GcTop5, 'points' => 50, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::GcTop5, 'points' => 35, 'difficulty' => null, 'position' => 4],
            ['type' => ScoringRuleType::GcTop5, 'points' => 25, 'difficulty' => null, 'position' => 5],
            ['type' => ScoringRuleType::GcTop5Partial, 'points' => 20, 'difficulty' => null, 'position' => null],

            ['type' => ScoringRuleType::PointsWinner, 'points' => 35, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::PointsWinner, 'points' => 25, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::PointsWinner, 'points' => 18, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::PointsWinnerPartial, 'points' => 12, 'difficulty' => null, 'position' => null],

            ['type' => ScoringRuleType::MountainsWinner, 'points' => 35, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::MountainsWinner, 'points' => 25, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::MountainsWinner, 'points' => 18, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::MountainsWinnerPartial, 'points' => 12, 'difficulty' => null, 'position' => null],

            ['type' => ScoringRuleType::YouthWinner, 'points' => 35, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::YouthWinner, 'points' => 25, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::YouthWinner, 'points' => 18, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::YouthWinnerPartial, 'points' => 12, 'difficulty' => null, 'position' => null],

            ['type' => ScoringRuleType::TeamsWinner, 'points' => 35, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::SuperCombativo, 'points' => 35, 'difficulty' => null, 'position' => null],
        ]);
    }

    private function insertRules(string $systemId, array $rules): void
    {
        $now = now();

        foreach ($rules as $rule) {
            DB::table('scoring_rules')->insert([
                'id' => Str::uuid()->toString(),
                'scoring_system_id' => $systemId,
                'type' => $rule['type']->value,
                'context' => $rule['type']->context()->value,
                'points' => $rule['points'],
                'difficulty' => $rule['difficulty'],
                'position' => $rule['position'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
