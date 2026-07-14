<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Application\Services\ActivityLogService;
use App\Application\Services\PushNotificationService;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\PredictionType;
use App\Domain\ValueObjects\StageStatus;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LockPredictionsCommand extends Command
{
    protected $signature = 'race:lock-predictions {stage_id? : The stage UUID to lock predictions for (omit for all stages with passed scheduled_start)}';

    protected $description = 'Lock predictions for stages that have started and update their status to ongoing';

    public function handle(ActivityLogService $activityLog, PushNotificationService $pushNotification): int
    {
        Log::info('race:lock-predictions started', [
            'now' => now()->toIso8601String(),
            'timezone' => now()->timezoneName,
        ]);

        $stageId = $this->argument('stage_id');

        if ($stageId) {
            $stages = StageModel::where('id', $stageId)->get();
        } else {
            $stages = StageModel::where('status', StageStatus::Upcoming)
                ->where(function ($query) {
                    $query->where('scheduled_start', '<=', now()->addMinutes(5))
                        ->orWhere(function ($q) {
                            $q->whereNull('scheduled_start')
                                ->where('date', '<=', now()->toDateString());
                        });
                })
                ->get();
        }

        Log::info("Found {$stages->count()} stages to process", [
            'stage_ids' => $stages->pluck('id', 'number')->toArray(),
            'stage_details' => $stages->map(fn ($s) => [
                'number' => $s->number,
                'name' => $s->name,
                'scheduled_start' => $s->scheduled_start?->toIso8601String(),
                'date' => $s->date?->format('Y-m-d'),
                'status' => $s->status->value,
            ])->toArray(),
        ]);

        if ($stages->isEmpty()) {
            $this->warn('No stages found to lock predictions for');

            return self::SUCCESS;
        }

        $locked = 0;

        foreach ($stages as $stage) {
            try {
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
                    $updated = $stage->update(['status' => StageStatus::Ongoing]);

                    Log::info("Stage {$stage->number} ({$stage->name}) status update result", [
                        'updated' => $updated,
                        'new_status' => $stage->fresh()->status->value,
                    ]);

                    $this->info("Stage status updated to ongoing: {$stage->name}");

                    $this->startEditionIfFirstStage($stage, $activityLog);
                }

                foreach ($stage->edition->leagues as $league) {
                    try {
                        if (! $activityLog->hasStageStartForLeague($league, $stage->id)) {
                            $activityLog->logStageStart($league, $stage);
                            $this->info("Logged stage_start for league {$league->id}");
                        }

                        if (! $activityLog->hasPredictionsLockedForLeague($league, $stage->id)) {
                            $topRiders = $this->getTopPredictedRiders($league->id, $stage->id);
                            if ($topRiders !== []) {
                                $activityLog->logPredictionsLocked($league, $stage, $topRiders);
                                $this->info("Logged predictions_locked for league {$league->id}");
                            }
                        }

                        $pushNotification->sendStageReminder($league, $stage);
                    } catch (\Throwable $e) {
                        Log::warning("Failed to process league {$league->id} for stage {$stage->number}", [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Failed to process stage {$stage->number} ({$stage->name})", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error("Failed to process stage {$stage->number}: {$e->getMessage()}");
            }
        }

        $this->info("Total locked: {$locked} predictions");
        Log::info('race:lock-predictions finished', ['locked' => $locked]);

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

    private function getTopPredictedRiders(string $leagueId, string $stageId): array
    {
        $predictions = PredictionModel::where('league_id', $leagueId)
            ->where('stage_id', $stageId)
            ->where('category', 'stage_winner')
            ->whereNotNull('locked_at')
            ->get();

        if ($predictions->isEmpty()) {
            return [];
        }

        $riderCounts = $predictions
            ->map(fn ($p) => $p->prediction_value['rider_id'] ?? null)
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(3);

        if ($riderCounts->isEmpty()) {
            return [];
        }

        $riderNames = RiderModel::whereIn('id', $riderCounts->keys())
            ->pluck('first_name', 'id');

        return $riderCounts->map(fn ($count, $id) => [
            'rider_id' => $id,
            'name' => $riderNames[$id] ?? '—',
            'count' => $count,
        ])->values()->all();
    }
}
