<?php

declare(strict_types=1);

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
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
        'country' => 'Francia',
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

    $league = LeagueModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Amigos del Tour',
        'edition_id' => $this->edition->id,
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
});

test('command warns when no editions found', function () {
    EditionModel::truncate();

    $this->artisan('race:score-pre-race')
        ->expectsOutputToContain('No editions found')
        ->assertExitCode(0);
});

test('command processes pre-race predictions for finished edition', function () {
    $this->artisan('race:score-pre-race')
        ->assertExitCode(0);
});

test('command processes specific edition', function () {
    $this->artisan('race:score-pre-race', ['edition_id' => $this->edition->id])
        ->assertExitCode(0);
});

test('command shows warning for unfinished editions when run without edition_id', function () {
    EditionModel::where('id', $this->edition->id)->update([
        'status' => EditionStatus::Upcoming,
    ]);

    $this->artisan('race:score-pre-race')
        ->assertExitCode(0);
});
