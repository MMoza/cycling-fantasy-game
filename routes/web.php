<?php

declare(strict_types=1);

use App\Presentation\Http\Controllers\Admin\CompetitionController;
use App\Presentation\Http\Controllers\Admin\CompetitionSetupController;
use App\Presentation\Http\Controllers\Admin\EditionController;
use App\Presentation\Http\Controllers\Admin\RiderController;
use App\Presentation\Http\Controllers\Admin\TeamController;
use App\Presentation\Http\Controllers\Admin\UserController;
use App\Presentation\Http\Controllers\ClassificationController;
use App\Presentation\Http\Controllers\DashboardController;
use App\Presentation\Http\Controllers\LeagueController;
use App\Presentation\Http\Controllers\PredictionController;
use App\Presentation\Http\Controllers\ProfileController;
use App\Presentation\Http\Controllers\StageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/leagues', [LeagueController::class, 'index'])->name('leagues.index');
    Route::get('/leagues/create', [LeagueController::class, 'create'])->name('leagues.create');
    Route::post('/leagues', [LeagueController::class, 'store'])->name('leagues.store');
    Route::get('/leagues/{league}', [LeagueController::class, 'show'])->name('leagues.show');
    Route::post('/leagues/join', [LeagueController::class, 'join'])->name('leagues.join');

    Route::get('/leagues/{league}/stage', [StageController::class, 'index'])->name('stages.index');
    Route::get('/leagues/{league}/stage/{stage}', [StageController::class, 'show'])->name('stages.show');
    Route::post('/leagues/{league}/stage/{stage}/predict', [PredictionController::class, 'store'])->name('predictions.store');

    Route::get('/leagues/{league}/predictions/pre-race', [PredictionController::class, 'preRace'])->name('predictions.pre-race');
    Route::post('/leagues/{league}/predictions/pre-race', [PredictionController::class, 'storePreRace'])->name('predictions.pre-race.store');

    Route::get('/leagues/{league}/classification', [ClassificationController::class, 'index'])->name('classification.index');
});

Route::middleware(['auth', 'verified', 'super-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Presentation\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Competitions
    Route::get('/competitions', [CompetitionController::class, 'index'])->name('competitions.index');
    Route::get('/competitions/create', [CompetitionController::class, 'create'])->name('competitions.create');
    Route::post('/competitions', [CompetitionController::class, 'store'])->name('competitions.store');
    Route::get('/competitions/{id}/edit', [CompetitionController::class, 'edit'])->name('competitions.edit');
    Route::patch('/competitions/{id}', [CompetitionController::class, 'update'])->name('competitions.update');

    // Editions
    Route::get('/competitions/{competitionId}/editions', [EditionController::class, 'index'])->name('competitions.editions.index');
    Route::get('/competitions/{competitionId}/editions/create', [EditionController::class, 'create'])->name('competitions.editions.create');
    Route::post('/competitions/{competitionId}/editions', [EditionController::class, 'store'])->name('competitions.editions.store');
    Route::get('/competitions/{competitionId}/editions/{id}/edit', [EditionController::class, 'edit'])->name('competitions.editions.edit');
    Route::patch('/competitions/{competitionId}/editions/{id}', [EditionController::class, 'update'])->name('competitions.editions.update');

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

require __DIR__.'/auth.php';
