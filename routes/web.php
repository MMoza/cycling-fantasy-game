<?php

declare(strict_types=1);

use App\Presentation\Http\Controllers\ClassificationController;
use App\Presentation\Http\Controllers\DashboardController;
use App\Presentation\Http\Controllers\LeagueController;
use App\Presentation\Http\Controllers\PredictionController;
use App\Presentation\Http\Controllers\ProfileController;
use App\Presentation\Http\Controllers\StageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
    Route::get('/', [\App\Presentation\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/competitions', [\App\Presentation\Http\Controllers\Admin\CompetitionController::class, 'index'])->name('competitions.index');
    Route::get('/competitions/create', [\App\Presentation\Http\Controllers\Admin\CompetitionController::class, 'create'])->name('competitions.create');
    Route::post('/competitions', [\App\Presentation\Http\Controllers\Admin\CompetitionController::class, 'store'])->name('competitions.store');
    Route::get('/competitions/{id}/edit', [\App\Presentation\Http\Controllers\Admin\CompetitionController::class, 'edit'])->name('competitions.edit');
    Route::patch('/competitions/{id}', [\App\Presentation\Http\Controllers\Admin\CompetitionController::class, 'update'])->name('competitions.update');

    Route::get('/competitions/{competitionId}/editions', [\App\Presentation\Http\Controllers\Admin\EditionController::class, 'index'])->name('competitions.editions.index');
    Route::get('/competitions/{competitionId}/editions/create', [\App\Presentation\Http\Controllers\Admin\EditionController::class, 'create'])->name('competitions.editions.create');
    Route::post('/competitions/{competitionId}/editions', [\App\Presentation\Http\Controllers\Admin\EditionController::class, 'store'])->name('competitions.editions.store');
    Route::get('/competitions/{competitionId}/editions/{id}/edit', [\App\Presentation\Http\Controllers\Admin\EditionController::class, 'edit'])->name('competitions.editions.edit');
    Route::patch('/competitions/{competitionId}/editions/{id}', [\App\Presentation\Http\Controllers\Admin\EditionController::class, 'update'])->name('competitions.editions.update');

    Route::get('/editions/{editionId}/stages', [\App\Presentation\Http\Controllers\Admin\StageController::class, 'index'])->name('editions.stages.index');
    Route::get('/editions/{editionId}/stages/create', [\App\Presentation\Http\Controllers\Admin\StageController::class, 'create'])->name('editions.stages.create');
    Route::post('/editions/{editionId}/stages', [\App\Presentation\Http\Controllers\Admin\StageController::class, 'store'])->name('editions.stages.store');
    Route::get('/editions/{editionId}/stages/{id}/edit', [\App\Presentation\Http\Controllers\Admin\StageController::class, 'edit'])->name('editions.stages.edit');
    Route::patch('/editions/{editionId}/stages/{id}', [\App\Presentation\Http\Controllers\Admin\StageController::class, 'update'])->name('editions.stages.update');

    Route::get('/users', [\App\Presentation\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/users/{id}/toggle-admin', [\App\Presentation\Http\Controllers\Admin\UserController::class, 'toggleAdmin'])->name('users.toggle-admin');
});

require __DIR__.'/auth.php';
