<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Application\Services\ActivityLogService;
use App\Domain\ValueObjects\ActivityLogType;
use App\Domain\ValueObjects\StageStatus;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillActivityLogsCommand extends Command
{
    protected $signature = 'race:backfill-activity-logs {edition_id? : The edition UUID to backfill (omit for all)}';

    protected $description = 'Backfill activity logs for historical data based on existing score events and stage results';

    public function handle(ActivityLogService $activityLog): int
    {
        $editionId = $this->argument('edition_id');

        $editions = EditionModel::with('competition')
            ->when($editionId, fn ($q) => $q->where('id', $editionId))
            ->whereHas('leagues')
            ->get();

        if ($editions->isEmpty()) {
            $this->warn('No editions with leagues found');

            return self::SUCCESS;
        }

        $created = 0;

        foreach ($editions as $edition) {
            $this->info("Processing edition: {$edition->competition->name} {$edition->year}");

            $leagues = LeagueModel::with('edition.competition')
                ->where('edition_id', $edition->id)
                ->get();

            // 1. Competition start (pre-race predictions scored)
            foreach ($leagues as $league) {
                $hasPreRaceScores = DB::table('score_events')
                    ->where('league_id', $league->id)
                    ->whereNull('stage_id')
                    ->exists();

                if ($hasPreRaceScores && ! $activityLog->hasTypeForLeague($league, ActivityLogType::CompetitionStart)) {
                    $activityLog->logCompetitionStart($league);
                    $created++;
                    $this->info("  competition_start for league {$league->name}");
                }

                // 2. Stage starts
                $stagesWithLocked = DB::table('predictions')
                    ->where('league_id', $league->id)
                    ->whereNotNull('locked_at')
                    ->whereNotNull('stage_id')
                    ->distinct()
                    ->pluck('stage_id');

                foreach ($stagesWithLocked as $stageId) {
                    $stage = StageModel::find($stageId);
                    if (! $stage || $activityLog->hasStageStartForLeague($league, $stageId)) {
                        continue;
                    }

                    $activityLog->logStageStart($league, $stage);
                    $created++;
                    $this->info("  stage_start for league {$league->name}, stage {$stage->number}");
                }

                // 3. Stage ends
                $stagesWithScores = DB::table('score_events')
                    ->where('league_id', $league->id)
                    ->whereNotNull('stage_id')
                    ->distinct()
                    ->pluck('stage_id');

                foreach ($stagesWithScores as $stageId) {
                    $stage = StageModel::find($stageId);
                    if (! $stage || $activityLog->hasStageEndForLeague($league, $stageId)) {
                        continue;
                    }

                    $activityLog->logStageEnd($league, $stage);
                    $created++;
                    $this->info("  stage_end for league {$league->name}, stage {$stage->number}");
                }
            }

            // 4. Competition end (all non-rest stages finished AND all leagues have stage scores)
            $allStagesFinished = StageModel::where('edition_id', $edition->id)
                ->where('type', '!=', 'rest')
                ->where('status', '!=', StageStatus::Finished)
                ->doesntExist();

            if ($allStagesFinished) {
                foreach ($leagues as $league) {
                    if (! $activityLog->hasTypeForLeague($league, ActivityLogType::CompetitionEnd)) {
                        $activityLog->logCompetitionEnd($league);
                        $created++;
                        $this->info("  competition_end for league {$league->name}");
                    }
                }
            }
        }

        $this->info("Backfill complete. Created {$created} activity log entries.");

        return self::SUCCESS;
    }
}
