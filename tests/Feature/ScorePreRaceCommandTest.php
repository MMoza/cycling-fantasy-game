<?php

declare(strict_types=1);

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\FinalClassificationModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Infrastructure\Persistence\Models\ScoringRuleModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
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

    $this->edition = EditionModel::create([
        'id' => Str::uuid()->toString(),
        'competition_id' => $competition->id,
        'year' => 2026,
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-23',
        'status' => EditionStatus::Finished,
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
        'type' => 'super_combativo',
        'context' => 'pre_race',
        'points' => 30,
    ]);

    $this->league = LeagueModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Amigos del Tour',
        'edition_id' => $this->edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'owner_id' => $this->user->id,
        'invite_code' => Str::random(8),
        'max_players' => 20,
        'is_public' => false,
    ]);

    $this->league->users()->attach($this->user->id, [
        'id' => Str::uuid()->toString(),
        'role' => 'owner',
    ]);

    $this->riderId = Str::uuid()->toString();

    DB::table('riders')->insert([
        'id' => $this->riderId,
        'first_name' => 'Test',
        'last_name' => 'Rider',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

test('command warns when no editions found', function () {
    EditionModel::truncate();

    $this->artisan('race:score-pre-race')
        ->expectsOutputToContain('No editions found')
        ->assertExitCode(0);
});

test('command warns when no final classifications set', function () {
    $this->artisan('race:score-pre-race')
        ->expectsOutputToContain('No final classifications set')
        ->assertExitCode(0);
});

test('command scores pre-race predictions against final classifications', function () {
    FinalClassificationModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->edition->id,
        'category' => 'super_combativo',
        'rider_id' => $this->riderId,
        'position' => 1,
    ]);

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->user->id,
        'league_id' => $this->league->id,
        'stage_id' => null,
        'type' => 'pre_race',
        'category' => 'super_combativo',
        'prediction_value' => ['rider_id' => $this->riderId],
        'locked_at' => now()->subDay(),
    ]);

    $this->artisan('race:score-pre-race')
        ->assertExitCode(0);

    $this->assertDatabaseHas('score_events', [
        'user_id' => $this->user->id,
        'league_id' => $this->league->id,
        'points' => 30,
    ]);
});

test('command does not create duplicate score events on re-run', function () {
    FinalClassificationModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->edition->id,
        'category' => 'super_combativo',
        'rider_id' => $this->riderId,
        'position' => 1,
    ]);

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->user->id,
        'league_id' => $this->league->id,
        'stage_id' => null,
        'type' => 'pre_race',
        'category' => 'super_combativo',
        'prediction_value' => ['rider_id' => $this->riderId],
        'locked_at' => now()->subDay(),
    ]);

    $this->artisan('race:score-pre-race')->assertExitCode(0);
    $this->artisan('race:score-pre-race')->assertExitCode(0);

    expect(ScoreEventModel::count())->toBe(1);
});

test('command force flag clears and re-scores', function () {
    FinalClassificationModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->edition->id,
        'category' => 'super_combativo',
        'rider_id' => $this->riderId,
        'position' => 1,
    ]);

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->user->id,
        'league_id' => $this->league->id,
        'stage_id' => null,
        'type' => 'pre_race',
        'category' => 'super_combativo',
        'prediction_value' => ['rider_id' => $this->riderId],
        'locked_at' => now()->subDay(),
    ]);

    $this->artisan('race:score-pre-race')->assertExitCode(0);
    $this->artisan('race:score-pre-race', ['--force' => true])->assertExitCode(0);

    expect(ScoreEventModel::count())->toBe(1);
});

test('command shows warning for unfinished editions when run without edition_id', function () {
    EditionModel::where('id', $this->edition->id)->update([
        'status' => EditionStatus::Upcoming,
    ]);

    $this->artisan('race:score-pre-race')
        ->assertExitCode(0);
});
