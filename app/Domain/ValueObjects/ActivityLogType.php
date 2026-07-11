<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum ActivityLogType: string
{
    case CompetitionStart = 'competition_start';
    case StageStart = 'stage_start';
    case StageEnd = 'stage_end';
    case CompetitionEnd = 'competition_end';
    case PredictionsLocked = 'predictions_locked';

    public function icon(): string
    {
        return match ($this) {
            self::CompetitionStart => 'flag',
            self::StageStart => 'play',
            self::StageEnd => 'check',
            self::CompetitionEnd => 'trophy',
            self::PredictionsLocked => 'lock',
        };
    }
}
