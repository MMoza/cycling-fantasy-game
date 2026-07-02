<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Application\Services\ActivityLogService;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\PredictionType;
use App\Domain\ValueObjects\StageStatus;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Console\Command;

class LockPredictionsCommand extends Command
{
    protected $signature = 'race:lock-predictions {stage_id? : The stage UUID to lock predictions for (omit for all stages with passed scheduled_start)}';

    protected $description = 'Lock predictions for stages that have started and update their status to ongoing';

    public function handle(ActivityLogService $activityLog): int
    {
        $stageId = $this->argument('stage_id');

        if ($stageId) {
            $stages = StageModel::where('id', $stageId)->get();
        } else {
            $stages = StageModel::where('status', StageStatus::Upcoming)
                ->where('scheduled_start', '<=', now())
                ->get();
        }

        if ($stages->isEmpty()) {
            $this->warn('No stages found to lock predictions for');

            return self::SUCCESS;
        }

        $locked = 0;

        foreach ($stages as $stage) {
            $stage->load('edition.leagues', 'edition.competition');

            $predictions = PredictionModel::where('stage_id', $stage->id)
                ->whereNull('locked_at')
                ->get();

            if ($predictions->isNotEmpty()) {
                foreach ($predictions as $prediction) {
                    $prediction->update([
                        'locked_at' => now(),
                    ]);

                    $locked++;
                }

                $this->info("Locked {$predictions->count()} predictions for stage: {$stage->name}");
            }

            if ($stage->status === StageStatus::Upcoming) {
                $stage->update(['status' => StageStatus::Ongoing]);

                foreach ($stage->edition->leagues as $league) {
                    if (! $activityLog->hasStageStartForLeague($league, $stage->id)) {
                        $activityLog->logStageStart($league, $stage);
                        $this->info("Logged stage_start for league {$league->id}");
                    }
                }

                $this->info("Stage status updated to ongoing: {$stage->name}");

                $this->startEditionIfFirstStage($stage, $activityLog);
            }
        }

        $this->info("Total locked: {$locked} predictions");

        return self::SUCCESS;
    }

    private function startEditionIfFirstStage(StageModel $stage, ActivityLogService $activityLog): void
    {
        $hasPreviousOngoing = StageModel::where('edition_id', $stage->edition_id)
            ->where('status', StageStatus::Ongoing)
            ->where('id', '!=', $stage->id)
            ->exists();

        if ($hasPreviousOngoing) {
            return;
        }

        $edition = $stage->edition;

        if ($edition->status !== EditionStatus::Upcoming) {
            return;
        }

        $edition->update(['status' => EditionStatus::Ongoing]);
        $this->info("Edition {$edition->id} started");

        $leagues = $edition->leagues;
        $leagueIds = $leagues->pluck('id');

        $preRacePredictions = PredictionModel::whereIn('league_id', $leagueIds)
            ->where('type', PredictionType::PreRace->value)
            ->whereNull('locked_at')
            ->get();

        $preRaceLocked = 0;
        foreach ($preRacePredictions as $prediction) {
            $prediction->update(['locked_at' => now()]);
            $preRaceLocked++;
        }

        if ($preRaceLocked > 0) {
            $this->info("Locked {$preRaceLocked} pre-race predictions across {$leagueIds->count()} leagues");
        }

        foreach ($leagues as $league) {
            $activityLog->logCompetitionStart($league);
            $this->info("Logged competition_start for league {$league->id}");
        }
    }
}
