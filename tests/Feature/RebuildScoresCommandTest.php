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
    $user = User::factory()->create();

    $competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Tour de Francia',
        'type' => CompetitionType::GrandTour,
        'country' => 'Francia',
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

    $this->league1 = LeagueModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Liga 1',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'owner_id' => $user->id,
        'invite_code' => Str::random(8),
        'max_players' => 20,
        'is_public' => false,
    ]);

    $this->league1->users()->attach($user->id, [
        'id' => Str::uuid()->toString(),
        'role' => 'owner',
    ]);

    $this->stage = StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $edition->id,
        'number' => 1,
        'name' => 'Etapa 1',
        'date' => '2026-07-01',
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

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'league_id' => $this->league1->id,
        'stage_id' => $this->stage->id,
        'type' => 'pre_stage',
        'category' => 'stage_winner',
        'prediction_value' => ['rider_id' => 'rider-1'],
        'locked_at' => now()->subDay(),
    ]);
});

test('command warns when no leagues found', function () {
    $this->artisan('race:rebuild-scores', ['league_id' => 'non-existent'])
        ->expectsOutputToContain('No leagues found')
        ->assertExitCode(0);
});

test('command rebuilds scores for a specific league', function () {
    ScoreEventModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->league1->owner_id,
        'league_id' => $this->league1->id,
        'scoring_rule_id' => Str::uuid()->toString(),
        'points' => 999,
        'description' => 'Stale event',
        'context' => 'stage_1',
    ]);

    $this->artisan('race:rebuild-scores', ['league_id' => $this->league1->id])
        ->assertExitCode(0);

    expect(ScoreEventModel::where('league_id', $this->league1->id)->count())->toBe(1);

    $this->assertDatabaseMissing('score_events', [
        'points' => 999,
    ]);
});

test('command rebuilds scores when league has no finished stages', function () {
    StageModel::where('id', $this->stage->id)->update([
        'status' => StageStatus::Upcoming,
    ]);

    $this->artisan('race:rebuild-scores', ['league_id' => $this->league1->id])
        ->expectsOutputToContain('No finished stages')
        ->assertExitCode(0);
});

test('command rebuilds scores for all leagues when no league_id specified', function () {
    $this->artisan('race:rebuild-scores')
        ->assertExitCode(0);

    expect(ScoreEventModel::count())->toBe(1);
});
