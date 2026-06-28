<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\UseCases\Admin\Competition\GetCompetitionFormDataUseCase;
use App\Application\UseCases\Admin\Competition\ListCompetitionsUseCase;
use App\Application\UseCases\Admin\Competition\StoreCompetitionUseCase;
use App\Application\UseCases\Admin\Competition\UpdateCompetitionUseCase;
use App\Application\UseCases\Admin\CompetitionSetup\AddTeamToCompetitionUseCase;
use App\Application\UseCases\Admin\CompetitionSetup\RemoveTeamFromCompetitionUseCase;
use App\Application\UseCases\Admin\CompetitionSetup\ShowSetupUseCase;
use App\Application\UseCases\Admin\CompetitionSetup\ToggleRiderUseCase;
use App\Application\UseCases\Admin\Edition\GetEditionFormDataUseCase;
use App\Application\UseCases\Admin\Edition\ListEditionsUseCase;
use App\Application\UseCases\Admin\Edition\StoreEditionUseCase;
use App\Application\UseCases\Admin\Edition\UpdateEditionUseCase;
use App\Application\UseCases\Admin\FinalClassification\GetFinalClassificationsUseCase;
use App\Application\UseCases\Admin\FinalClassification\UpdateFinalClassificationsUseCase;
use App\Application\UseCases\Admin\Rider\GetRiderFormDataUseCase;
use App\Application\UseCases\Admin\Rider\ListRidersUseCase;
use App\Application\UseCases\Admin\Rider\StoreRiderUseCase;
use App\Application\UseCases\Admin\Rider\UpdateRiderUseCase;
use App\Application\UseCases\Admin\ShowAdminDashboardUseCase;
use App\Application\UseCases\Admin\Stage\GetStageFormDataUseCase;
use App\Application\UseCases\Admin\Stage\ListAdminStagesUseCase;
use App\Application\UseCases\Admin\Stage\MarkStageFinishedUseCase;
use App\Application\UseCases\Admin\Stage\MarkStageUpcomingUseCase;
use App\Application\UseCases\Admin\Stage\ShowAdminStageUseCase;
use App\Application\UseCases\Admin\Stage\StoreStageResultUseCase;
use App\Application\UseCases\Admin\Stage\StoreStageUseCase;
use App\Application\UseCases\Admin\Stage\UpdateStageUseCase;
use App\Application\UseCases\Admin\Team\AddRiderToTeamUseCase;
use App\Application\UseCases\Admin\Team\GetTeamFormDataUseCase;
use App\Application\UseCases\Admin\Team\ListTeamsUseCase;
use App\Application\UseCases\Admin\Team\RemoveRiderFromTeamUseCase;
use App\Application\UseCases\Admin\Team\ShowTeamUseCase;
use App\Application\UseCases\Admin\Team\StoreTeamUseCase;
use App\Application\UseCases\Admin\Team\UpdateTeamUseCase;
use App\Application\UseCases\Admin\User\ListUsersUseCase;
use App\Application\UseCases\Admin\User\ToggleAdminUseCase;
use App\Application\UseCases\Classification\ShowClassificationUseCase;
use App\Application\UseCases\Dashboard\ShowDashboardUseCase;
use App\Application\UseCases\League\CreateLeagueUseCase;
use App\Application\UseCases\League\GetCreateLeagueFormDataUseCase;
use App\Application\UseCases\League\JoinLeagueUseCase;
use App\Application\UseCases\League\ListLeaguesUseCase;
use App\Application\UseCases\League\ShowLeagueUseCase;
use App\Application\UseCases\Prediction\ShowPreRaceFormUseCase;
use App\Application\UseCases\Prediction\StorePreRacePredictionUseCase;
use App\Application\UseCases\Prediction\StoreStagePredictionUseCase;
use App\Application\UseCases\Stage\ListStagesUseCase;
use App\Application\UseCases\Stage\ShowStageUseCase;
use App\Domain\Interfaces\CyclingDataFetcherInterface;
use App\Infrastructure\Services\MockCyclingDataFetcher;
use App\Presentation\Console\ImportResultsCommand;
use App\Presentation\Console\ImportStagesCommand;
use App\Presentation\Console\Deploy\AppDeployCommand;
use App\Presentation\Console\LockPredictionsCommand;
use App\Presentation\Console\RebuildScoresCommand;
use App\Presentation\Console\ScorePreRaceCommand;
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
            AppDeployCommand::class,
            ImportStagesCommand::class,
            ImportResultsCommand::class,
            ScoreStageCommand::class,
            RebuildScoresCommand::class,
            ScorePreRaceCommand::class,
            LockPredictionsCommand::class,
        ]);
    }
}
