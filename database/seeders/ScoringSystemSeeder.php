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

        $rules = [
            // Stage scoring by difficulty
            // 1-star stages
            ['type' => ScoringRuleType::StageWinner, 'points' => 10, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 2, 'difficulty' => 1, 'position' => null],
            // 2-star stages
            ['type' => ScoringRuleType::StageWinner, 'points' => 20, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 4, 'difficulty' => 2, 'position' => null],
            // 3-star stages
            ['type' => ScoringRuleType::StageWinner, 'points' => 30, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 6, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageSecond, 'points' => 15, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageThird, 'points' => 10, 'difficulty' => 3, 'position' => null],
            // Stage leader (same for all difficulties)
            ['type' => ScoringRuleType::StageLeader, 'points' => 5, 'difficulty' => null, 'position' => null],

            // Pre-race: GC Top 5
            ['type' => ScoringRuleType::GcTop5, 'points' => 100, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::GcTop5, 'points' => 75, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::GcTop5, 'points' => 50, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::GcTop5, 'points' => 30, 'difficulty' => null, 'position' => 4],
            ['type' => ScoringRuleType::GcTop5, 'points' => 20, 'difficulty' => null, 'position' => 5],
            ['type' => ScoringRuleType::GcTop5Partial, 'points' => 15, 'difficulty' => null, 'position' => null],

            // Pre-race: Points (Green) classification
            ['type' => ScoringRuleType::PointsWinner, 'points' => 40, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::PointsWinner, 'points' => 25, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::PointsWinner, 'points' => 15, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::PointsWinnerPartial, 'points' => 10, 'difficulty' => null, 'position' => null],

            // Pre-race: Mountains (Polka dot) classification
            ['type' => ScoringRuleType::MountainsWinner, 'points' => 40, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::MountainsWinner, 'points' => 25, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::MountainsWinner, 'points' => 15, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::MountainsWinnerPartial, 'points' => 10, 'difficulty' => null, 'position' => null],

            // Pre-race: Youth (White) classification
            ['type' => ScoringRuleType::YouthWinner, 'points' => 40, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::YouthWinner, 'points' => 25, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::YouthWinner, 'points' => 15, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::YouthWinnerPartial, 'points' => 10, 'difficulty' => null, 'position' => null],

            // Pre-race: Teams and Super Combativo
            ['type' => ScoringRuleType::TeamsWinner, 'points' => 30, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::SuperCombativo, 'points' => 30, 'difficulty' => null, 'position' => null],
        ];

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
