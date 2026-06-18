<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Interfaces\CyclingDataFetcherInterface;
use App\Infrastructure\Services\MockCyclingDataFetcher;
use App\Presentation\Console\ImportResultsCommand;
use App\Presentation\Console\ImportStagesCommand;
use App\Presentation\Console\LockPredictionsCommand;
use App\Presentation\Console\RebuildScoresCommand;
use App\Presentation\Console\ScoreStageCommand;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CyclingDataFetcherInterface::class, MockCyclingDataFetcher::class);
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        $this->commands([
            ImportStagesCommand::class,
            ImportResultsCommand::class,
            ScoreStageCommand::class,
            RebuildScoresCommand::class,
            LockPredictionsCommand::class,
        ]);
    }
}
