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
        $this->addRulesToSystem(ScoringSystemType::Standard->value, $this->getStandardPartialRules());
        $this->addRulesToSystem(ScoringSystemType::Aggressive->value, $this->getAggressivePartialRules());
        $this->addRulesToSystem(ScoringSystemType::Conservative->value, $this->getConservativePartialRules());
        $this->addRulesToSystem(ScoringSystemType::OneWeek->value, $this->getOneWeekPartialRules());
    }

    public function down(): void
    {
        DB::table('scoring_rules')
            ->where('type', ScoringRuleType::StageTop3Partial->value)
            ->delete();
    }

    private function addRulesToSystem(string $systemType, array $rules): void
    {
        $systemId = DB::table('scoring_systems')->where('type', $systemType)->value('id');
        if (! $systemId) {
            return;
        }

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

    private function getStandardPartialRules(): array
    {
        return [
            ['type' => ScoringRuleType::StageTop3Partial, 'points' => 5, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageTop3Partial, 'points' => 8, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageTop3Partial, 'points' => 10, 'difficulty' => 3, 'position' => null],
        ];
    }

    private function getAggressivePartialRules(): array
    {
        return [
            ['type' => ScoringRuleType::StageTop3Partial, 'points' => 5, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageTop3Partial, 'points' => 10, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageTop3Partial, 'points' => 15, 'difficulty' => 3, 'position' => null],
        ];
    }

    private function getConservativePartialRules(): array
    {
        return [
            ['type' => ScoringRuleType::StageTop3Partial, 'points' => 4, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageTop3Partial, 'points' => 8, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageTop3Partial, 'points' => 10, 'difficulty' => 3, 'position' => null],
        ];
    }

    private function getOneWeekPartialRules(): array
    {
        return [
            ['type' => ScoringRuleType::StageTop3Partial, 'points' => 4, 'difficulty' => 1, 'position' => null],
            ['type' => ScoringRuleType::StageTop3Partial, 'points' => 7, 'difficulty' => 2, 'position' => null],
            ['type' => ScoringRuleType::StageTop3Partial, 'points' => 10, 'difficulty' => 3, 'position' => null],
        ];
    }
};
