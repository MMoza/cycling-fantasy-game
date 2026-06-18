<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Domain\ValueObjects\PredictionType;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Console\Command;

class LockPredictionsCommand extends Command
{
    protected $signature = 'race:lock-predictions {stage_id? : The stage UUID to lock predictions for (omit for all upcoming stages)}';

    protected $description = 'Lock predictions for a stage before it starts';

    public function handle(): int
    {
        $stageId = $this->argument('stage_id');

        if ($stageId) {
            $stages = StageModel::where('id', $stageId)->get();
        } else {
            $stages = StageModel::where('status', 'upcoming')
                ->where('date', '<=', now()->addHour())
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

            if ($predictions->isEmpty()) {
                $this->info("No unlocked predictions for stage: {$stage->name}");

                continue;
            }

            foreach ($predictions as $prediction) {
                $prediction->update([
                    'locked_at' => now(),
                ]);

                $locked++;
            }

            $this->info("Locked {$predictions->count()} predictions for stage: {$stage->name}");
        }

        $this->info("Total locked: {$locked} predictions");

        return self::SUCCESS;
    }
}
