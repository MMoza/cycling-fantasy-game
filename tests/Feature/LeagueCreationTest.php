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
    $user = User::factory()->create(['plan' => 'premium']);

    $competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Tour de Francia',
        'type' => CompetitionType::GrandTour,
        'country_id' => createCountry(),
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
    $user = User::factory()->create(['plan' => 'premium']);

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

    $response = $this->actingAs($user)->post(route('leagues.store'), [
        'name' => 'Amigos del Tour',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
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
        'is_official' => false,
        'is_public' => true,
    ]);

    $this->assertDatabaseHas('league_user', [
        'league_id' => $leagueId,
        'user_id' => $user->id,
        'role' => 'owner',
    ]);
});

test('free user cannot create a league', function () {
    $user = User::factory()->create(['plan' => 'free']);

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

    $response = $this->actingAs($user)->post(route('leagues.store'), [
        'name' => 'Amigos del Tour',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'is_public' => true,
    ]);

    $response->assertSessionHasErrors('plan');
    $this->assertDatabaseMissing('leagues', ['name' => 'Amigos del Tour']);
});

test('admin can create official league with conservative scoring', function () {
    $user = User::factory()->create(['plan' => 'free', 'is_admin' => true]);

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
        'name' => 'Conservador',
        'type' => ScoringSystemType::Conservative,
        'description' => 'Puntuación más repartida',
    ]);

    $response = $this->actingAs($user)->post(route('leagues.store'), [
        'name' => 'Liga Oficial',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'is_public' => false,
        'is_official' => true,
    ]);

    $response->assertSessionHasNoErrors();

    $leagueId = DB::table('leagues')->where('name', 'Liga Oficial')->value('id');

    $this->assertDatabaseHas('leagues', [
        'id' => $leagueId,
        'name' => 'Liga Oficial',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'owner_id' => $user->id,
        'is_official' => true,
        'is_public' => true,
    ]);
});

test('free user cannot join non-official league', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $freeUser = User::factory()->create(['plan' => 'free']);

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

    $league = DB::table('leagues')->insertGetId([
        'id' => Str::uuid()->toString(),
        'name' => 'Liga Amigos',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'owner_id' => $admin->id,
        'invite_code' => 'TESTCODE',
        'is_official' => false,
        'is_public' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($freeUser)->post(route('leagues.join'), [
        'invite_code' => 'TESTCODE',
    ]);

    $response->assertSessionHasErrors('invite_code');
});

test('free user can join official league', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $freeUser = User::factory()->create(['plan' => 'free']);

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
        'name' => 'Conservador',
        'type' => ScoringSystemType::Conservative,
        'description' => 'Puntuación más repartida',
    ]);

    $leagueId = Str::uuid()->toString();
    DB::table('leagues')->insert([
        'id' => $leagueId,
        'name' => 'Liga Oficial',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'owner_id' => $admin->id,
        'invite_code' => 'OFFICODE',
        'is_official' => true,
        'is_public' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($freeUser)->post(route('leagues.join'), [
        'invite_code' => 'OFFICODE',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('league_user', [
        'league_id' => $leagueId,
        'user_id' => $freeUser->id,
        'role' => 'member',
    ]);
});
