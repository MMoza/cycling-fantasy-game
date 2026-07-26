<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Application\Services\PreRaceScoringService;
use App\Domain\ValueObjects\EditionStatus;
use App\Infrastructure\Persistence\Models\EditionModel;
use Illuminate\Console\Command;

class ScorePreRaceCommand extends Command
{
    protected $signature = 'race:score-pre-race {edition_id? : The edition UUID to score (omit for all finished editions)}
        {--force : Re-score even if already scored}';

    protected $description = 'Calculate scores for all pre-race predictions of an edition';

    public function handle(PreRaceScoringService $preRaceScoring): int
    {
        $editionId = $this->argument('edition_id');

        if ($editionId) {
            $editions = EditionModel::where('id', $editionId)->get();
        } else {
            $editions = EditionModel::where('status', EditionStatus::Finished)->get();
        }

        if ($editions->isEmpty()) {
            $this->warn('No editions found to score');

            return self::SUCCESS;
        }

        $grandTotal = 0;

        foreach ($editions as $edition) {
            $this->info("Scoring pre-race predictions for edition: {$edition->year}");

            $result = $preRaceScoring->scoreEdition(
                $edition->id,
                force: (bool) $this->option('force'),
            );

            if ($result['scored'] === 0 && $result['leagues'] === 0 && $result['skipped'] === 0) {
                $this->warn("No final classifications set for edition: {$edition->year}. Set them via admin panel first.");

                continue;
            }

            $this->info("  Score events: {$result['scored']}, Leagues scored: {$result['leagues']}, Skipped: {$result['skipped']}");
            $grandTotal += $result['scored'];
        }

        $this->info("Pre-race scoring complete. Total score events created: {$grandTotal}");

        return self::SUCCESS;
    }
}
