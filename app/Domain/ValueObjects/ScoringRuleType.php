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
    case StageExactPos1 = 'stage_exact_pos_1';
    case StageExactPos2 = 'stage_exact_pos_2';
    case StageExactPos3 = 'stage_exact_pos_3';
    case StageExactPos4 = 'stage_exact_pos_4';
    case StageExactPos5 = 'stage_exact_pos_5';
    case StageExactPos6 = 'stage_exact_pos_6';
    case StageExactPos7 = 'stage_exact_pos_7';
    case StageExactPos8 = 'stage_exact_pos_8';
    case StageExactPos9 = 'stage_exact_pos_9';
    case StageExactPos10 = 'stage_exact_pos_10';
    case StagePartialPos1 = 'stage_partial_pos_1';
    case StagePartialPos2 = 'stage_partial_pos_2';
    case StagePartialPos3 = 'stage_partial_pos_3';
    case StagePartialPos4 = 'stage_partial_pos_4';
    case StagePartialPos5 = 'stage_partial_pos_5';
    case StagePartialPos6 = 'stage_partial_pos_6';
    case StagePartialPos7 = 'stage_partial_pos_7';
    case StagePartialPos8 = 'stage_partial_pos_8';
    case StagePartialPos9 = 'stage_partial_pos_9';
    case StagePartialPos10 = 'stage_partial_pos_10';
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
            self::StageExactPos1 => '1º clasificado (exacto)',
            self::StageExactPos2 => '2º clasificado (exacto)',
            self::StageExactPos3 => '3º clasificado (exacto)',
            self::StageExactPos4 => '4º clasificado (exacto)',
            self::StageExactPos5 => '5º clasificado (exacto)',
            self::StageExactPos6 => '6º clasificado (exacto)',
            self::StageExactPos7 => '7º clasificado (exacto)',
            self::StageExactPos8 => '8º clasificado (exacto)',
            self::StageExactPos9 => '9º clasificado (exacto)',
            self::StageExactPos10 => '10º clasificado (exacto)',
            self::StagePartialPos1 => '1º clasificado (parcial)',
            self::StagePartialPos2 => '2º clasificado (parcial)',
            self::StagePartialPos3 => '3º clasificado (parcial)',
            self::StagePartialPos4 => '4º clasificado (parcial)',
            self::StagePartialPos5 => '5º clasificado (parcial)',
            self::StagePartialPos6 => '6º clasificado (parcial)',
            self::StagePartialPos7 => '7º clasificado (parcial)',
            self::StagePartialPos8 => '8º clasificado (parcial)',
            self::StagePartialPos9 => '9º clasificado (parcial)',
            self::StagePartialPos10 => '10º clasificado (parcial)',
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
            self::StageCombativo,
            self::StageExactPos1,
            self::StageExactPos2,
            self::StageExactPos3,
            self::StageExactPos4,
            self::StageExactPos5,
            self::StageExactPos6,
            self::StageExactPos7,
            self::StageExactPos8,
            self::StageExactPos9,
            self::StageExactPos10,
            self::StagePartialPos1,
            self::StagePartialPos2,
            self::StagePartialPos3,
            self::StagePartialPos4,
            self::StagePartialPos5,
            self::StagePartialPos6,
            self::StagePartialPos7,
            self::StagePartialPos8,
            self::StagePartialPos9,
            self::StagePartialPos10 => ScoringRuleContext::PreStage,
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
