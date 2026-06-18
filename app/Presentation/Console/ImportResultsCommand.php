<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Domain\Entities\StageResult;
use App\Domain\Interfaces\CyclingDataFetcherInterface;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Console\Command;

class ImportResultsCommand extends Command
{
    protected $signature = 'race:import-results {stage_id : The stage UUID to import results for}';

    protected $description = 'Import results for a stage from the cycling data source';

    public function __construct(
        private readonly CyclingDataFetcherInterface $fetcher,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $stageId = $this->argument('stage_id');

        $stage = StageModel::find($stageId);

        if (! $stage) {
            $this->error("Stage not found: {$stageId}");

            return self::FAILURE;
        }

        $this->info("Importing results for: {$stage->name}");

        $resultsData = $this->fetcher->fetchStageResults($stageId);

        $bar = $this->output->createProgressBar(count($resultsData));
        $bar->start();

        $imported = 0;

        foreach ($resultsData as $resultData) {
            $existing = \Illuminate\Support\Facades\DB::table('stage_results')
                ->where('stage_id', $stageId)
                ->where('position', $resultData['position'])
                ->first();

            if ($existing) {
                $this->warn("Position {$resultData['position']} already exists, skipping");
                $bar->advance();

                continue;
            }

            $result = StageResult::create(
                stageId: $stageId,
                riderId: $resultData['rider_id'],
                position: $resultData['position'],
                time: $resultData['time'],
                gap: $resultData['gap'],
            );

            \Illuminate\Support\Facades\DB::table('stage_results')->insert([
                'id' => $result->id,
                'stage_id' => $stageId,
                'rider_id' => $result->riderId,
                'position' => $result->position,
                'time' => $result->time,
                'gap' => $result->gap,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $imported++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Imported {$imported} results for stage {$stage->name}");

        return self::SUCCESS;
    }
}
