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
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Infrastructure\Persistence\Models\ScoringRuleModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

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

    ScoringRuleModel::create([
        'id' => Str::uuid()->toString(),
        'scoring_system_id' => $scoringSystem->id,
        'type' => 'stage_winner',
        'context' => 'pre_stage',
        'points' => 50,
    ]);

    ScoringRuleModel::create([
        'id' => Str::uuid()->toString(),
        'scoring_system_id' => $scoringSystem->id,
        'type' => 'stage_second',
        'context' => 'pre_stage',
        'points' => 30,
    ]);

    ScoringRuleModel::create([
        'id' => Str::uuid()->toString(),
        'scoring_system_id' => $scoringSystem->id,
        'type' => 'stage_third',
        'context' => 'pre_stage',
        'points' => 20,
    ]);

    $this->league = LeagueModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Amigos del Tour',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'owner_id' => $this->user->id,
        'invite_code' => Str::random(8),
        'is_public' => false,
    ]);

    $this->league->users()->attach($this->user->id, [
        'id' => Str::uuid()->toString(),
        'role' => 'owner',
    ]);

    $this->stage = StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $edition->id,
        'number' => 1,
        'name' => 'Etapa 1',
        'date' => '2026-07-01',
        'scheduled_start' => '2026-07-01 11:00:00',
        'type' => StageType::Flat,
        'distance' => 180.5,
        'origin' => 'Lille',
        'destination' => 'Paris',
        'status' => StageStatus::Finished,
    ]);

    DB::table('stage_results')->insert([
        'id' => Str::uuid()->toString(),
        'stage_id' => $this->stage->id,
        'rider_id' => 'rider-1',
        'position' => 1,
        'time' => '4:30:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('stage_results')->insert([
        'id' => Str::uuid()->toString(),
        'stage_id' => $this->stage->id,
        'rider_id' => 'rider-2',
        'position' => 2,
        'time' => '4:31:00',
        'gap' => '+1:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('stage_results')->insert([
        'id' => Str::uuid()->toString(),
        'stage_id' => $this->stage->id,
        'rider_id' => 'rider-3',
        'position' => 3,
        'time' => '4:32:00',
        'gap' => '+2:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->user->id,
        'league_id' => $this->league->id,
        'stage_id' => $this->stage->id,
        'type' => 'pre_stage',
        'category' => 'stage_winner',
        'prediction_value' => ['rider_id' => 'rider-1'],
        'locked_at' => now()->subDay(),
    ]);

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->user->id,
        'league_id' => $this->league->id,
        'stage_id' => $this->stage->id,
        'type' => 'pre_stage',
        'category' => 'stage_second',
        'prediction_value' => ['rider_id' => 'rider-2'],
        'locked_at' => now()->subDay(),
    ]);

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->user->id,
        'league_id' => $this->league->id,
        'stage_id' => $this->stage->id,
        'type' => 'pre_stage',
        'category' => 'stage_third',
        'prediction_value' => ['rider_id' => 'rider-99'],
        'locked_at' => now()->subDay(),
    ]);
});

test('command fails when stage not found', function () {
    $this->artisan('race:score-stage', ['stage_id' => 'non-existent'])
        ->expectsOutputToContain('Stage not found')
        ->assertExitCode(1);
});

test('command fails when no results found', function () {
    $newStage = StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->stage->edition_id,
        'number' => 99,
        'name' => 'Etapa sin resultados',
        'date' => '2026-07-05',
        'type' => StageType::Flat,
        'distance' => 150,
        'origin' => 'A',
        'destination' => 'B',
        'status' => StageStatus::Finished,
    ]);

    $this->artisan('race:score-stage', ['stage_id' => $newStage->id])
        ->expectsOutputToContain('No results found')
        ->assertExitCode(1);
});

test('command scores correct predictions with matching results', function () {
    $this->artisan('race:score-stage', ['stage_id' => $this->stage->id])
        ->assertExitCode(0);

    expect(ScoreEventModel::count())->toBe(2);

    $this->assertDatabaseHas('score_events', [
        'points' => 50,
    ]);

    $this->assertDatabaseHas('score_events', [
        'points' => 30,
    ]);

    $this->assertDatabaseMissing('score_events', [
        'points' => 20,
    ]);
});

test('command does not create duplicate score events on re-run without force', function () {
    $this->artisan('race:score-stage', ['stage_id' => $this->stage->id])
        ->assertExitCode(0);

    $this->artisan('race:score-stage', ['stage_id' => $this->stage->id])
        ->assertExitCode(0);

    expect(ScoreEventModel::count())->toBe(2);
});

test('command force flag clears and re-scores', function () {
    $this->artisan('race:score-stage', ['stage_id' => $this->stage->id])
        ->assertExitCode(0);

    $this->artisan('race:score-stage', ['stage_id' => $this->stage->id, '--force' => true])
        ->assertExitCode(0);

    expect(ScoreEventModel::count())->toBe(2);
});

test('command warns when no predictions exist', function () {
    PredictionModel::truncate();

    $this->artisan('race:score-stage', ['stage_id' => $this->stage->id])
        ->expectsOutputToContain('No predictions found')
        ->assertExitCode(0);
});

test('command scores predictions for all users in a league without duplicates', function () {
    $otherUser = User::factory()->create();
    $this->league->users()->attach($otherUser->id, [
        'id' => Str::uuid()->toString(),
        'role' => 'member',
    ]);

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $otherUser->id,
        'league_id' => $this->league->id,
        'stage_id' => $this->stage->id,
        'type' => 'pre_stage',
        'category' => 'stage_winner',
        'prediction_value' => ['rider_id' => 'rider-1'],
        'locked_at' => now()->subDay(),
    ]);

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $otherUser->id,
        'league_id' => $this->league->id,
        'stage_id' => $this->stage->id,
        'type' => 'pre_stage',
        'category' => 'stage_second',
        'prediction_value' => ['rider_id' => 'rider-2'],
        'locked_at' => now()->subDay(),
    ]);

    $this->artisan('race:score-stage', ['stage_id' => $this->stage->id])
        ->assertExitCode(0);

    expect(ScoreEventModel::count())->toBe(4);

    $this->artisan('race:score-stage', ['stage_id' => $this->stage->id])
        ->assertExitCode(0);

    expect(ScoreEventModel::count())->toBe(4);
});
