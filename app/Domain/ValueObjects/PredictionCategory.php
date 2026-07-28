<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum PredictionCategory: string
{
    case GcTop5 = 'gc_top_5';
    case PointsWinner = 'points_winner';
    case MountainsWinner = 'mountains_winner';
    case YouthWinner = 'youth_winner';
    case TeamsWinner = 'teams_winner';
    case SuperCombativo = 'super_combativo';
    case StageWinner = 'stage_winner';
    case StageSecond = 'stage_second';
    case StageThird = 'stage_third';
    case StageLeader = 'stage_leader';
    case StageCombativo = 'stage_combativo';
    case StagePosition1 = 'stage_position_1';
    case StagePosition2 = 'stage_position_2';
    case StagePosition3 = 'stage_position_3';
    case StagePosition4 = 'stage_position_4';
    case StagePosition5 = 'stage_position_5';
    case StagePosition6 = 'stage_position_6';
    case StagePosition7 = 'stage_position_7';
    case StagePosition8 = 'stage_position_8';
    case StagePosition9 = 'stage_position_9';
    case StagePosition10 = 'stage_position_10';

    public function label(): string
    {
        return match ($this) {
            self::GcTop5 => 'Top 5 clasificación general',
            self::PointsWinner => 'Ganador maillot verde',
            self::MountainsWinner => 'Ganador maillot montaña',
            self::YouthWinner => 'Ganador maillot blanco',
            self::TeamsWinner => 'Ganador clasificación equipos',
            self::SuperCombativo => 'Supercombativo final',
            self::StageWinner => 'Ganador de etapa',
            self::StageSecond => '2º clasificado etapa',
            self::StageThird => '3º clasificado etapa',
            self::StageLeader => 'Líder GC tras etapa',
            self::StageCombativo => 'Combativo del día',
            self::StagePosition1 => '1º clasificado',
            self::StagePosition2 => '2º clasificado',
            self::StagePosition3 => '3º clasificado',
            self::StagePosition4 => '4º clasificado',
            self::StagePosition5 => '5º clasificado',
            self::StagePosition6 => '6º clasificado',
            self::StagePosition7 => '7º clasificado',
            self::StagePosition8 => '8º clasificado',
            self::StagePosition9 => '9º clasificado',
            self::StagePosition10 => '10º clasificado',
        };
    }

    public function context(): ScoringRuleContext
    {
        return match ($this) {
            self::GcTop5,
            self::PointsWinner,
            self::MountainsWinner,
            self::YouthWinner,
            self::TeamsWinner,
            self::SuperCombativo => ScoringRuleContext::PreRace,
            self::StageWinner,
            self::StageSecond,
            self::StageThird,
            self::StageLeader,
            self::StageCombativo,
            self::StagePosition1,
            self::StagePosition2,
            self::StagePosition3,
            self::StagePosition4,
            self::StagePosition5,
            self::StagePosition6,
            self::StagePosition7,
            self::StagePosition8,
            self::StagePosition9,
            self::StagePosition10 => ScoringRuleContext::PreStage,
        };
    }
}
