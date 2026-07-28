<?php

use App\Domain\ValueObjects\ScoringRuleType;
use App\Domain\ValueObjects\ScoringSystemType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->updateStandardSystem();
        $this->createOneWeekSystem();
        $this->createOneDaySystem();
    }

    public function down(): void
    {
        DB::table('scoring_systems')->where('type', ScoringSystemType::OneWeek->value)->delete();
        DB::table('scoring_systems')->where('type', ScoringSystemType::OneDay->value)->delete();

        $standardId = DB::table('scoring_systems')->where('type', ScoringSystemType::Standard->value)->value('id');
        if ($standardId) {
            DB::table('scoring_rules')->where('scoring_system_id', $standardId)->delete();
            $this->insertRules($standardId, $this->getOldStandardRules());
        }
    }

    private function updateStandardSystem(): void
    {
        $standardId = DB::table('scoring_systems')->where('type', ScoringSystemType::Standard->value)->value('id');
        if (! $standardId) {
            return;
        }

        DB::table('scoring_rules')->where('scoring_system_id', $standardId)->delete();
        $this->insertRules($standardId, $this->getNewStandardRules());
    }

    private function createOneWeekSystem(): void
    {
        if (DB::table('scoring_systems')->where('type', ScoringSystemType::OneWeek->value)->exists()) {
            return;
        }

        $systemId = Str::uuid()->toString();

        DB::table('scoring_systems')->insert([
            'id' => $systemId,
            'name' => 'Carrera de una semana',
            'type' => ScoringSystemType::OneWeek->value,
            'description' => 'Puntuación para carreras de una semana. Solo clasificación general sin maillots.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->insertRules($systemId, $this->getOneWeekRules());
    }

    private function createOneDaySystem(): void
    {
        if (DB::table('scoring_systems')->where('type', ScoringSystemType::OneDay->value)->exists()) {
            return;
        }

        $systemId = Str::uuid()->toString();

        DB::table('scoring_systems')->insert([
            'id' => $systemId,
            'name' => 'Carrera de un día',
            'type' => ScoringSystemType::OneDay->value,
            'description' => 'Puntuación para monumentos, campeonatos y clásicas. Predicciones por posición (1º a 10º clasificado).',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->insertRules($systemId, $this->getOneDayRules());
    }

    private function getNewStandardRules(): array
    {
        return [
            ['type' => ScoringRuleType::StageWinner, 'points' => 10, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageSecond, 'points' => 6, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageThird, 'points' => 4, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 3, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageWinner, 'points' => 18, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageSecond, 'points' => 12, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageThird, 'points' => 8, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 5, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageWinner, 'points' => 25, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageSecond, 'points' => 18, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageThird, 'points' => 12, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 8, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageLeader, 'points' => 8, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::GcTop5, 'points' => 100, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::GcTop5, 'points' => 75, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::GcTop5, 'points' => 55, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::GcTop5, 'points' => 35, 'difficulty' => null, 'position' => 4],
            ['type' => ScoringRuleType::GcTop5, 'points' => 25, 'difficulty' => null, 'position' => 5],
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
            ['type' => ScoringRuleType::TeamsWinner, 'points' => 25, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::SuperCombativo, 'points' => 25, 'difficulty' => null, 'position' => null],
        ];
    }

    private function getOldStandardRules(): array
    {
        return [
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
        ];
    }

    private function getOneWeekRules(): array
    {
        return [
            ['type' => ScoringRuleType::StageWinner, 'points' => 8, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageSecond, 'points' => 5, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageThird, 'points' => 3, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 2, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageWinner, 'points' => 14, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageSecond, 'points' => 9, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageThird, 'points' => 6, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 4, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageWinner, 'points' => 20, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageSecond, 'points' => 14, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageThird, 'points' => 9, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageCombativo, 'points' => 6, 'difficulty' => 3, 'position' => null],
            ['type' => ScoringRuleType::StageLeader, 'points' => 6, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::GcTop5, 'points' => 70, 'difficulty' => null, 'position' => 1],
            ['type' => ScoringRuleType::GcTop5, 'points' => 50, 'difficulty' => null, 'position' => 2],
            ['type' => ScoringRuleType::GcTop5, 'points' => 35, 'difficulty' => null, 'position' => 3],
            ['type' => ScoringRuleType::GcTop5Partial, 'points' => 15, 'difficulty' => null, 'position' => null],
        ];
    }

    private function getOneDayRules(): array
    {
        return [
            ['type' => ScoringRuleType::StageExactPos1, 'points' => 100, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StageExactPos2, 'points' => 70, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StageExactPos3, 'points' => 50, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StageExactPos4, 'points' => 25, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StageExactPos5, 'points' => 20, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StageExactPos6, 'points' => 15, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StageExactPos7, 'points' => 12, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StageExactPos8, 'points' => 9, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StageExactPos9, 'points' => 6, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StageExactPos10, 'points' => 4, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StagePartialPos1, 'points' => 35, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StagePartialPos2, 'points' => 15, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StagePartialPos3, 'points' => 12, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StagePartialPos4, 'points' => 8, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StagePartialPos5, 'points' => 6, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StagePartialPos6, 'points' => 4, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StagePartialPos7, 'points' => 3, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StagePartialPos8, 'points' => 2, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StagePartialPos9, 'points' => 1, 'difficulty' => null, 'position' => null],
            ['type' => ScoringRuleType::StagePartialPos10, 'points' => 1, 'difficulty' => null, 'position' => null],
        ];
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
};
