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
    case GcTop5Partial = 'gc_top_5_partial';
    case PointsWinner = 'points_winner';
    case PointsWinnerPartial = 'points_winner_partial';
    case MountainsWinner = 'mountains_winner';
    case MountainsWinnerPartial = 'mountains_winner_partial';
    case YouthWinner = 'youth_winner';
    case YouthWinnerPartial = 'youth_winner_partial';
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
            self::GcTop5Partial => 'Top 5 (posición incorrecta)',
            self::PointsWinner => 'Ganador maillot verde',
            self::PointsWinnerPartial => 'Maillot verde (posición incorrecta)',
            self::MountainsWinner => 'Ganador maillot montaña',
            self::MountainsWinnerPartial => 'Maillot montaña (posición incorrecta)',
            self::YouthWinner => 'Ganador maillot blanco',
            self::YouthWinnerPartial => 'Maillot blanco (posición incorrecta)',
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
            self::GcTop5Partial,
            self::PointsWinner,
            self::PointsWinnerPartial,
            self::MountainsWinner,
            self::MountainsWinnerPartial,
            self::YouthWinner,
            self::YouthWinnerPartial,
            self::TeamsWinner,
            self::SuperCombativo => ScoringRuleContext::PreRace,
        };
    }
}
