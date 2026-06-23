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

require __DIR__.'/auth.php';
