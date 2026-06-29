<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Entities\ActivityLog;
use App\Domain\ValueObjects\ActivityLogType;
use App\Infrastructure\Persistence\Models\ActivityLogModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\StageModel;

class ActivityLogService
{
    public function logCompetitionStart(LeagueModel $league): void
    {
        $edition = $league->edition;

        $log = ActivityLog::create(
            leagueId: $league->id,
            type: ActivityLogType::CompetitionStart,
            title: "Comienza {$edition->competition->name} {$edition->year}",
            description: 'Se han bloqueado los pronósticos pre-carrera de todos los participantes.',
            data: [
                'edition_id' => $edition->id,
                'member_count' => $league->users()->count(),
            ],
        );

        ActivityLogModel::create([
            'id' => $log->id,
            'league_id' => $log->leagueId,
            'type' => $log->type,
            'title' => $log->title,
            'description' => $log->description,
            'data' => $log->data,
        ]);
    }

    public function logStageStart(LeagueModel $league, StageModel $stage): void
    {
        $log = ActivityLog::create(
            leagueId: $league->id,
            type: ActivityLogType::StageStart,
            title: "Etapa {$stage->number}: {$stage->name}",
            description: "Comienza la etapa {$stage->number} ({$stage->origin} → {$stage->destination}). Pronósticos bloqueados.",
            data: [
                'stage_id' => $stage->id,
                'stage_number' => $stage->number,
                'stage_name' => $stage->name,
            ],
        );

        ActivityLogModel::create([
            'id' => $log->id,
            'league_id' => $log->leagueId,
            'type' => $log->type,
            'title' => $log->title,
            'description' => $log->description,
            'data' => $log->data,
        ]);
    }

    public function logStageEnd(LeagueModel $league, StageModel $stage): void
    {
        $log = ActivityLog::create(
            leagueId: $league->id,
            type: ActivityLogType::StageEnd,
            title: "Finaliza la etapa {$stage->number}: {$stage->name}",
            description: "Resultados y puntuaciones de la etapa {$stage->number} disponibles.",
            data: [
                'stage_id' => $stage->id,
                'stage_number' => $stage->number,
                'stage_name' => $stage->name,
            ],
        );

        ActivityLogModel::create([
            'id' => $log->id,
            'league_id' => $log->leagueId,
            'type' => $log->type,
            'title' => $log->title,
            'description' => $log->description,
            'data' => $log->data,
        ]);
    }

    public function logCompetitionEnd(LeagueModel $league): void
    {
        $edition = $league->edition;

        $log = ActivityLog::create(
            leagueId: $league->id,
            type: ActivityLogType::CompetitionEnd,
            title: "Finaliza {$edition->competition->name} {$edition->year}",
            description: 'Clasificación final y puntuaciones disponibles.',
            data: [
                'edition_id' => $edition->id,
            ],
        );

        ActivityLogModel::create([
            'id' => $log->id,
            'league_id' => $log->leagueId,
            'type' => $log->type,
            'title' => $log->title,
            'description' => $log->description,
            'data' => $log->data,
        ]);
    }

    public function hasTypeForLeague(LeagueModel $league, ActivityLogType $type): bool
    {
        return ActivityLogModel::where('league_id', $league->id)
            ->where('type', $type->value)
            ->exists();
    }

    public function hasStageEndForLeague(LeagueModel $league, string $stageId): bool
    {
        return ActivityLogModel::where('league_id', $league->id)
            ->where('type', ActivityLogType::StageEnd->value)
            ->where('data->stage_id', $stageId)
            ->exists();
    }

    public function hasStageStartForLeague(LeagueModel $league, string $stageId): bool
    {
        return ActivityLogModel::where('league_id', $league->id)
            ->where('type', ActivityLogType::StageStart->value)
            ->where('data->stage_id', $stageId)
            ->exists();
    }
}
