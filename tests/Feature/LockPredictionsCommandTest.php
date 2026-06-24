<?php

declare(strict_types=1);

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create();

    $competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Tour de Francia',
        'type' => CompetitionType::GrandTour,
        'country_id' => createCountry(),
        'active' => true,
    ]);

    $edition = EditionModel::create([
        'id' => Str::uuid()->toString(),
        'competition_id' => $competition->id,
        'year' => 2026,
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-23',
        'status' => EditionStatus::Upcoming,
    ]);

    $scoringSystem = ScoringSystemModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Estándar',
        'type' => ScoringSystemType::Standard,
        'description' => 'Puntuación equilibrada',
    ]);

    $league = LeagueModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Amigos del Tour',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'owner_id' => $user->id,
        'invite_code' => Str::random(8),
        'max_players' => 20,
        'is_public' => false,
    ]);

    $league->users()->attach($user->id, [
        'id' => Str::uuid()->toString(),
        'role' => 'owner',
    ]);

    $this->startedStage = StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $edition->id,
        'number' => 1,
        'name' => 'Etapa 1',
        'date' => '2026-07-01',
        'scheduled_start' => now()->subHour(),
        'type' => StageType::Flat,
        'distance' => 180.5,
        'origin' => 'Lille',
        'destination' => 'Paris',
        'status' => StageStatus::Upcoming,
    ]);

    $this->futureStage = StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $edition->id,
        'number' => 2,
        'name' => 'Etapa 2',
        'date' => '2026-07-02',
        'scheduled_start' => now()->addDay(),
        'type' => StageType::Mountain,
        'distance' => 200.0,
        'origin' => 'Paris',
        'destination' => 'Lyon',
        'status' => StageStatus::Upcoming,
    ]);

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'league_id' => $league->id,
        'stage_id' => $this->startedStage->id,
        'type' => 'pre_stage',
        'category' => 'stage_winner',
        'prediction_value' => 'Rider A',
    ]);

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'league_id' => $league->id,
        'stage_id' => $this->startedStage->id,
        'type' => 'pre_stage',
        'category' => 'stage_second',
        'prediction_value' => 'Rider B',
    ]);

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'league_id' => $league->id,
        'stage_id' => $this->futureStage->id,
        'type' => 'pre_stage',
        'category' => 'stage_winner',
        'prediction_value' => 'Rider C',
    ]);
});

test('command warns when no stages match', function () {
    StageModel::where('id', $this->startedStage->id)->update([
        'scheduled_start' => now()->addDay(),
    ]);
    StageModel::where('id', $this->futureStage->id)->update([
        'scheduled_start' => now()->addDay(),
    ]);

    $this->artisan('race:lock-predictions')
        ->expectsOutputToContain('No stages found')
        ->assertExitCode(0);
});

test('command locks predictions for stages with passed scheduled_start', function () {
    $this->artisan('race:lock-predictions')
        ->assertExitCode(0);

    $lockedCount = PredictionModel::where('stage_id', $this->startedStage->id)
        ->whereNotNull('locked_at')
        ->count();

    expect($lockedCount)->toBe(2);

    $futureLocked = PredictionModel::where('stage_id', $this->futureStage->id)
        ->whereNotNull('locked_at')
        ->count();

    expect($futureLocked)->toBe(0);
});

test('command updates stage status to ongoing for started stages', function () {
    $this->artisan('race:lock-predictions')
        ->assertExitCode(0);

    $this->assertDatabaseHas('stages', [
        'id' => $this->startedStage->id,
        'status' => StageStatus::Ongoing,
    ]);

    $this->assertDatabaseHas('stages', [
        'id' => $this->futureStage->id,
        'status' => StageStatus::Upcoming,
    ]);
});

test('command skips already locked predictions', function () {
    PredictionModel::where('stage_id', $this->startedStage->id)
        ->where('category', 'stage_winner')
        ->update(['locked_at' => now()->subMinutes(30)]);

    $this->artisan('race:lock-predictions')
        ->assertExitCode(0);

    $lockedCount = PredictionModel::where('stage_id', $this->startedStage->id)
        ->whereNotNull('locked_at')
        ->count();

    expect($lockedCount)->toBe(2);
});

test('command accepts specific stage_id argument', function () {
    $this->artisan('race:lock-predictions', ['stage_id' => $this->futureStage->id])
        ->assertExitCode(0);

    $locked = PredictionModel::where('stage_id', $this->futureStage->id)
        ->whereNotNull('locked_at')
        ->count();

    expect($locked)->toBe(1);
});
