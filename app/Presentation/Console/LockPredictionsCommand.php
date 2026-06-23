<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Domain\ValueObjects\StageStatus;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Console\Command;

class LockPredictionsCommand extends Command
{
    protected $signature = 'race:lock-predictions {stage_id? : The stage UUID to lock predictions for (omit for all stages with passed scheduled_start)}';

    protected $description = 'Lock predictions for stages that have started and update their status to ongoing';

    public function handle(): int
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
                $this->info("Stage status updated to ongoing: {$stage->name}");
            }
        }

        $this->info("Total locked: {$locked} predictions");

        return self::SUCCESS;
    }
}
