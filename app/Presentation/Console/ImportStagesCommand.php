<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Domain\Entities\Stage;
use App\Domain\Interfaces\CyclingDataFetcherInterface;
use App\Infrastructure\Persistence\Models\EditionModel;
use Illuminate\Console\Command;

class ImportStagesCommand extends Command
{
    protected $signature = 'race:import-stages {edition_id : The edition UUID to import stages for}';

    protected $description = 'Import stages for an edition from the cycling data source';

    public function __construct(
        private readonly CyclingDataFetcherInterface $fetcher,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $editionId = $this->argument('edition_id');

        $edition = EditionModel::find($editionId);

        if (! $edition) {
            $this->error("Edition not found: {$editionId}");

            return self::FAILURE;
        }

        $this->info("Importing stages for: {$edition->name} ({$edition->year})");

        $stagesData = $this->fetcher->fetchStages($editionId);

        $bar = $this->output->createProgressBar(count($stagesData));
        $bar->start();

        $imported = 0;

        foreach ($stagesData as $stageData) {
            $existing = \Illuminate\Support\Facades\DB::table('stages')
                ->where('edition_id', $editionId)
                ->where('number', $stageData['number'])
                ->first();

            if ($existing) {
                $this->warn("Stage {$stageData['number']} already exists, skipping");
                $bar->advance();

                continue;
            }

            $stage = Stage::create(
                editionId: $editionId,
                number: $stageData['number'],
                name: $stageData['name'],
                date: $stageData['date'],
                type: $stageData['type'],
                distance: $stageData['distance'],
                origin: $stageData['origin'],
                destination: $stageData['destination'],
            );

            \Illuminate\Support\Facades\DB::table('stages')->insert([
                'id' => $stage->id,
                'edition_id' => $editionId,
                'number' => $stage->number,
                'name' => $stage->name,
                'date' => $stage->date,
                'type' => $stage->type->value,
                'distance' => $stage->distance,
                'origin' => $stage->origin,
                'destination' => $stage->destination,
                'status' => 'upcoming',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $imported++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Imported {$imported} stages for edition {$edition->name}");

        return self::SUCCESS;
    }
}
