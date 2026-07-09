<?php

declare(strict_types=1);

use App\Presentation\Http\Controllers\Admin\CompetitionController;
use App\Presentation\Http\Controllers\Admin\CompetitionSetupController;
use App\Presentation\Http\Controllers\Admin\EditionController;
use App\Presentation\Http\Controllers\Admin\FinalClassificationController;
use App\Presentation\Http\Controllers\Admin\RiderController;
use App\Presentation\Http\Controllers\Admin\TeamController;
use App\Presentation\Http\Controllers\Admin\UserController;
use App\Presentation\Http\Controllers\Auth\SocialiteController;
use App\Presentation\Http\Controllers\ClassificationController;
use App\Presentation\Http\Controllers\CompetitionController as UserCompetitionController;
use App\Presentation\Http\Controllers\DashboardController;
use App\Presentation\Http\Controllers\LandingController;
use App\Presentation\Http\Controllers\LeagueController;
use App\Presentation\Http\Controllers\PedalesController;
use App\Presentation\Http\Controllers\PredictionController;
use App\Presentation\Http\Controllers\ProfileController;
use App\Presentation\Http\Controllers\PushSubscriptionController;
use App\Presentation\Http\Controllers\RiderController as LeagueRiderController;
use App\Presentation\Http\Controllers\SearchController;
use App\Presentation\Http\Controllers\SeasonController;
use App\Presentation\Http\Controllers\StageController;
use App\Presentation\Http\Controllers\TeamController as LeagueTeamController;
use App\Presentation\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::get('/pedales', [PedalesController::class, 'index'])
    ->middleware(['auth'])
    ->name('pedales');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/leagues', [LeagueController::class, 'index'])->name('leagues.index');
    Route::get('/leagues/create', [LeagueController::class, 'create'])->name('leagues.create');
    Route::post('/leagues', [LeagueController::class, 'store'])->name('leagues.store');
    Route::get('/leagues/{league}', [LeagueController::class, 'show'])->name('leagues.show');
    Route::post('/leagues/join', [LeagueController::class, 'join'])->name('leagues.join');
    Route::post('/leagues/join-official', [LeagueController::class, 'joinOfficial'])->name('leagues.join-official');
    Route::match(['put', 'patch'], '/leagues/{league}', [LeagueController::class, 'update'])->name('leagues.update');

    Route::get('/leagues/{league}/stage', [StageController::class, 'index'])->name('stages.index');
    Route::get('/leagues/{league}/stage/{stage}', [StageController::class, 'show'])->name('stages.show');
    Route::post('/leagues/{league}/stage/{stage}/predict', [PredictionController::class, 'store'])->name('predictions.store');

    Route::get('/leagues/{league}/predictions/pre-race', [PredictionController::class, 'preRace'])->name('predictions.pre-race');
    Route::post('/leagues/{league}/predictions/pre-race', [PredictionController::class, 'storePreRace'])->name('predictions.pre-race.store');

    Route::get('/leagues/{league}/classification', [ClassificationController::class, 'index'])->name('classification.index');
    Route::get('/leagues/{league}/teams', [LeagueTeamController::class, 'index'])->name('leagues.teams');
    Route::get('/leagues/{league}/riders/{rider}', [LeagueRiderController::class, 'show'])->name('leagues.riders.show');
    Route::get('/leagues/{league}/members/{member}', [UserProfileController::class, 'show'])->name('leagues.members.show');

    Route::get('/search', [SearchController::class, '__invoke'])->name('search');

    Route::get('/push/vapid-key', [PushSubscriptionController::class, 'vapidKey'])->name('push.vapid-key');
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
    Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');

    Route::get('/competitions', [UserCompetitionController::class, 'index'])->name('competitions.index');
    Route::get('/competitions/{year?}', [UserCompetitionController::class, 'index'])->name('competitions.year');
    Route::get('/competitions/e/{edition}', [UserCompetitionController::class, 'show'])->name('competitions.show');

    Route::get('/season', [SeasonController::class, 'index'])->name('season.index');
    Route::get('/season/classification', [SeasonController::class, 'classification'])->name('season.classification');
});

Route::middleware(['auth', 'super-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Presentation\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Competitions
    Route::get('/competitions', [CompetitionController::class, 'index'])->name('competitions.index');
    Route::get('/competitions/create', [CompetitionController::class, 'create'])->name('competitions.create');
    Route::post('/competitions', [CompetitionController::class, 'store'])->name('competitions.store');
    Route::get('/competitions/{id}/edit', [CompetitionController::class, 'edit'])->name('competitions.edit');
    Route::patch('/competitions/{id}', [CompetitionController::class, 'update'])->name('competitions.update');
    Route::delete('/competitions/{id}', [CompetitionController::class, 'destroy'])->name('competitions.destroy');

    // Editions
    Route::get('/competitions/{competitionId}/editions', [EditionController::class, 'index'])->name('competitions.editions.index');
    Route::get('/competitions/{competitionId}/editions/create', [EditionController::class, 'create'])->name('competitions.editions.create');
    Route::post('/competitions/{competitionId}/editions', [EditionController::class, 'store'])->name('competitions.editions.store');
    Route::get('/competitions/{competitionId}/editions/{id}/edit', [EditionController::class, 'edit'])->name('competitions.editions.edit');
    Route::patch('/competitions/{competitionId}/editions/{id}', [EditionController::class, 'update'])->name('competitions.editions.update');

    // Final classifications
    Route::get('/editions/{editionId}/final-classifications', [FinalClassificationController::class, 'edit'])->name('editions.final-classifications');
    Route::post('/editions/{editionId}/final-classifications', [FinalClassificationController::class, 'update'])->name('editions.final-classifications.update');

    // Competition Setup (participants)
    Route::get('/competitions/{competitionId}/editions/{editionId}/setup', [CompetitionSetupController::class, 'show'])->name('competitions.setup');
    Route::post('/competitions/{competitionId}/editions/{editionId}/teams', [CompetitionSetupController::class, 'addTeam'])->name('competitions.setup.add-team');
    Route::delete('/competitions/{competitionId}/editions/{editionId}/teams/{teamId}', [CompetitionSetupController::class, 'removeTeam'])->name('competitions.setup.remove-team');
    Route::post('/competitions/{competitionId}/editions/{editionId}/riders/toggle', [CompetitionSetupController::class, 'toggleRider'])->name('competitions.setup.toggle-rider');

    // Stages
    Route::get('/editions/{editionId}/stages', [App\Presentation\Http\Controllers\Admin\StageController::class, 'index'])->name('editions.stages.index');
    Route::get('/editions/{editionId}/stages/create', [App\Presentation\Http\Controllers\Admin\StageController::class, 'create'])->name('editions.stages.create');
    Route::post('/editions/{editionId}/stages', [App\Presentation\Http\Controllers\Admin\StageController::class, 'store'])->name('editions.stages.store');
    Route::get('/editions/{editionId}/stages/{id}', [App\Presentation\Http\Controllers\Admin\StageController::class, 'show'])->name('editions.stages.show');
    Route::get('/editions/{editionId}/stages/{id}/edit', [App\Presentation\Http\Controllers\Admin\StageController::class, 'edit'])->name('editions.stages.edit');
    Route::patch('/editions/{editionId}/stages/{id}', [App\Presentation\Http\Controllers\Admin\StageController::class, 'update'])->name('editions.stages.update');
    Route::post('/editions/{editionId}/stages/{id}/finish', [App\Presentation\Http\Controllers\Admin\StageController::class, 'markFinished'])->name('editions.stages.finish');
    Route::post('/editions/{editionId}/stages/{id}/upcoming', [App\Presentation\Http\Controllers\Admin\StageController::class, 'markUpcoming'])->name('editions.stages.upcoming');
    Route::post('/editions/{editionId}/stages/{id}/results', [App\Presentation\Http\Controllers\Admin\StageController::class, 'storeResult'])->name('editions.stages.results');

    // Teams
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::get('/teams/{id}', [TeamController::class, 'show'])->name('teams.show');
    Route::get('/teams/{id}/edit', [TeamController::class, 'edit'])->name('teams.edit');
    Route::patch('/teams/{id}', [TeamController::class, 'update'])->name('teams.update');
    Route::post('/teams/{id}/rosters', [TeamController::class, 'addRider'])->name('teams.rosters.add');
    Route::delete('/teams/{id}/rosters/{riderId}/{year}', [TeamController::class, 'removeRider'])->name('teams.rosters.remove');

    // Riders
    Route::get('/riders', [RiderController::class, 'index'])->name('riders.index');
    Route::get('/riders/create', [RiderController::class, 'create'])->name('riders.create');
    Route::post('/riders', [RiderController::class, 'store'])->name('riders.store');
    Route::get('/riders/{id}/edit', [RiderController::class, 'edit'])->name('riders.edit');
    Route::patch('/riders/{id}', [RiderController::class, 'update'])->name('riders.update');

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/{id}/toggle-admin', [UserController::class, 'toggleAdmin'])->name('users.toggle-admin');
});

Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])->name('socialite.redirect');
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])->name('socialite.callback');

require __DIR__.'/auth.php';
