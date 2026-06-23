<?php

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('authenticated user can access league creation page', function () {
    $user = User::factory()->create();

    $competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Tour de Francia',
        'type' => CompetitionType::GrandTour,
        'country' => 'Francia',
        'active' => true,
    ]);

    EditionModel::create([
        'id' => Str::uuid()->toString(),
        'competition_id' => $competition->id,
        'year' => 2026,
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-23',
        'status' => EditionStatus::Upcoming,
    ]);

    ScoringSystemModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Estándar',
        'type' => ScoringSystemType::Standard,
        'description' => 'Puntuación equilibrada',
    ]);

    $response = $this->actingAs($user)->get(route('leagues.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Leagues/Create')
        ->has('editions', 1)
        ->has('scoringSystems', 1)
    );
});

test('authenticated user can create a league', function () {
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

    $response = $this->actingAs($user)->post(route('leagues.store'), [
        'name' => 'Amigos del Tour',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'max_players' => 25,
        'is_public' => true,
    ]);

    $response->assertSessionHasNoErrors();

    $leagueId = DB::table('leagues')->where('name', 'Amigos del Tour')->value('id');

    expect($leagueId)->not->toBeNull();

    $response->assertRedirect(route('leagues.show', $leagueId));

    $this->assertDatabaseHas('leagues', [
        'id' => $leagueId,
        'name' => 'Amigos del Tour',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'owner_id' => $user->id,
        'max_players' => 25,
        'is_public' => true,
    ]);

    $this->assertDatabaseHas('league_user', [
        'league_id' => $leagueId,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);
});
