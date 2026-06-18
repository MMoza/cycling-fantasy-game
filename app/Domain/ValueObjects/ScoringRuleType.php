<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum ScoringRuleType: string
{
    case StageWinner = 'stage_winner';
    case StageSecond = 'stage_second';
    case StageThird = 'stage_third';
    case StageLeader = 'stage_leader';
    case StageCombativo = 'stage_combativo';
    case GcTop5 = 'gc_top_5';
    case PointsWinner = 'points_winner';
    case MountainsWinner = 'mountains_winner';
    case YouthWinner = 'youth_winner';
    case TeamsWinner = 'teams_winner';
    case SuperCombativo = 'super_combativo';

    public function label(): string
    {
        return match ($this) {
            self::StageWinner => 'Ganador de etapa',
            self::StageSecond => '2º clasificado etapa',
            self::StageThird => '3º clasificado etapa',
            self::StageLeader => 'Líder GC tras etapa',
            self::StageCombativo => 'Combativo del día',
            self::GcTop5 => 'Top 5 clasificación general',
            self::PointsWinner => 'Ganador maillot verde',
            self::MountainsWinner => 'Ganador maillot montaña',
            self::YouthWinner => 'Ganador maillot blanco',
            self::TeamsWinner => 'Ganador clasificación equipos',
            self::SuperCombativo => 'Supercombativo final',
        };
    }

    public function context(): ScoringRuleContext
    {
        return match ($this) {
            self::StageWinner,
            self::StageSecond,
            self::StageThird,
            self::StageLeader,
            self::StageCombativo => ScoringRuleContext::PreStage,
            self::GcTop5,
            self::PointsWinner,
            self::MountainsWinner,
            self::YouthWinner,
            self::TeamsWinner,
            self::SuperCombativo => ScoringRuleContext::PreRace,
        };
    }
}
