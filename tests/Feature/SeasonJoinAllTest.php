<?php

use App\Domain\ValueObjects\EditionStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('joins all missing official leagues of the season', function () {
    $user = User::factory()->create(['plan' => 'free']);

    createSeasonLeague('Liga Tour', 2027, '2027-07-01', competitionName: 'Tour de Francia');
    createSeasonLeague('Liga Giro', 2027, '2027-05-01', competitionName: 'Giro de Italia');
    createSeasonLeague('Liga Vuelta', 2027, '2027-08-01', competitionName: 'Vuelta a España');

    $response = $this->actingAs($user)
        ->from(route('season.index', 2027))
        ->post(route('season.join-all'), ['year' => 2027]);

    $response->assertRedirect(route('season.index', 2027));

    expect($user->leagues()->count())->toBe(3);
    expect(session('inertia.flash_data')['success'] ?? null)->toBe('Te has unido a 3 competiciones.');
});

test('skips already joined leagues and finished editions', function () {
    $user = User::factory()->create(['plan' => 'free']);

    $joined = createSeasonLeague('Liga Tour', 2027, '2027-07-01', competitionName: 'Tour de Francia');
    joinSeasonLeague($joined['league'], $user);

    $finished = createSeasonLeague('Liga Giro', 2027, '2027-05-01', competitionName: 'Giro de Italia', status: EditionStatus::Finished);
    $pending = createSeasonLeague('Liga Vuelta', 2027, '2027-08-01', competitionName: 'Vuelta a España');

    $this->actingAs($user)->post(route('season.join-all'), ['year' => 2027]);

    $leagueIds = $user->leagues()->pluck('leagues.id')->all();

    expect($leagueIds)->toContain($joined['league']->id);
    expect($leagueIds)->toContain($pending['league']->id);
    expect($leagueIds)->not->toContain($finished['league']->id);
    expect(session('inertia.flash_data')['success'] ?? null)->toBe('Te has unido a 1 competición.');
});

test('keeps the joined leagues when the plan limit is reached', function () {
    $user = User::factory()->create(['plan' => 'free']);

    foreach (range(1, 7) as $i) {
        createSeasonLeague("Liga {$i}", 2027, "2027-07-0{$i}", competitionName: "Competición {$i}");
    }

    $this->actingAs($user)->post(route('season.join-all'), ['year' => 2027]);

    expect($user->leagues()->count())->toBe(5);

    $error = session('inertia.flash_data')['error'] ?? null;
    expect($error)->toContain('Te has unido a 5 competiciones.');
    expect($error)->toContain('Has alcanzado el límite');
});

test('ignores inactive competitions', function () {
    $user = User::factory()->create(['plan' => 'free']);

    $inactive = createSeasonLeague('Liga Inactiva', 2027, '2027-07-01', competitionName: 'Competición Inactiva');
    $inactive['edition']->competition->update(['active' => false]);

    $active = createSeasonLeague('Liga Activa', 2027, '2027-08-01', competitionName: 'Competición Activa');

    $this->actingAs($user)->post(route('season.join-all'), ['year' => 2027]);

    $leagueIds = $user->leagues()->pluck('leagues.id')->all();

    expect($leagueIds)->toContain($active['league']->id);
    expect($leagueIds)->not->toContain($inactive['league']->id);
});

test('reports when the user is already in every available competition', function () {
    $user = User::factory()->create(['plan' => 'free']);

    $league = createSeasonLeague('Liga Tour', 2027, '2027-07-01', competitionName: 'Tour de Francia');
    joinSeasonLeague($league['league'], $user);

    $this->actingAs($user)->post(route('season.join-all'), ['year' => 2027]);

    expect($user->leagues()->count())->toBe(1);
    expect(session('inertia.flash_data')['success'] ?? null)
        ->toBe('Ya estás inscrito en todas las competiciones disponibles.');
});

test('join all requires authentication', function () {
    $response = $this->post(route('season.join-all'), ['year' => 2027]);

    $response->assertRedirect(route('login'));
});

test('join all validates the year', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('season.join-all'), ['year' => 'nope'])
        ->assertSessionHasErrors('year');
});
