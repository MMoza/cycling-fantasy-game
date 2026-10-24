<?php

use App\Application\UseCases\League\GetUserSeasonLeaguesUseCase;
use App\Infrastructure\Services\S3MediaUrlResolver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('returns only the user leagues from the current season', function () {
    $user = User::factory()->create();

    $currentYear = (int) date('Y');

    $season = createSeasonLeague('Liga Temporada', $currentYear, $currentYear.'-07-01', $user);
    createSeasonLeague('Liga Año Pasado', $currentYear - 1, ($currentYear - 1).'-07-01');

    $useCase = new GetUserSeasonLeaguesUseCase(new S3MediaUrlResolver);
    $result = $useCase->execute($user);

    expect($result)->toHaveCount(1);
    expect($result[0]['id'])->toBe($season['league']->id);
    expect($result[0]['name'])->toBe('Liga Temporada');
    expect($result[0]['year'])->toBe($currentYear);
});

test('excludes leagues the user does not belong to', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $currentYear = (int) date('Y');

    $mine = createSeasonLeague('Mi Liga', $currentYear, $currentYear.'-06-01', $user);
    createSeasonLeague('Liga Ajena', $currentYear, $currentYear.'-07-01', $otherUser);

    $useCase = new GetUserSeasonLeaguesUseCase(new S3MediaUrlResolver);
    $result = $useCase->execute($user);

    expect($result)->toHaveCount(1);
    expect($result[0]['id'])->toBe($mine['league']->id);
});

test('orders leagues by edition start date', function () {
    $user = User::factory()->create();

    $currentYear = (int) date('Y');

    $later = createSeasonLeague('Vuelta', $currentYear, $currentYear.'-08-15', $user);
    $earlier = createSeasonLeague('Tour', $currentYear, $currentYear.'-07-01', $user);

    $useCase = new GetUserSeasonLeaguesUseCase(new S3MediaUrlResolver);
    $result = $useCase->execute($user);

    expect($result)->toHaveCount(2);
    expect($result[0]['id'])->toBe($earlier['league']->id);
    expect($result[1]['id'])->toBe($later['league']->id);
});

test('appends the active league from another season at the end', function () {
    $user = User::factory()->create();

    $currentYear = (int) date('Y');

    $season = createSeasonLeague('Liga Temporada', $currentYear, $currentYear.'-07-01', $user);
    $past = createSeasonLeague('Liga Histórica', $currentYear - 1, ($currentYear - 1).'-07-01', $user);

    $useCase = new GetUserSeasonLeaguesUseCase(new S3MediaUrlResolver);
    $result = $useCase->execute($user, $past['league']->id);

    expect($result)->toHaveCount(2);
    expect($result[0]['id'])->toBe($season['league']->id);
    expect($result[1]['id'])->toBe($past['league']->id);
    expect($result[1]['year'])->toBe($currentYear - 1);
});

test('ignores an active league the user does not belong to', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $currentYear = (int) date('Y');

    $season = createSeasonLeague('Liga Temporada', $currentYear, $currentYear.'-07-01', $user);
    $foreign = createSeasonLeague('Liga Ajena', $currentYear - 1, ($currentYear - 1).'-07-01', $otherUser);

    $useCase = new GetUserSeasonLeaguesUseCase(new S3MediaUrlResolver);
    $result = $useCase->execute($user, $foreign['league']->id);

    expect($result)->toHaveCount(1);
    expect($result[0]['id'])->toBe($season['league']->id);
});

test('returns the season league payload shape', function () {
    $user = User::factory()->create();

    $currentYear = (int) date('Y');

    $season = createSeasonLeague('Liga Temporada', $currentYear, $currentYear.'-07-01', $user);
    $friend = User::factory()->create();
    joinSeasonLeague($season['league'], $friend);

    $useCase = new GetUserSeasonLeaguesUseCase(new S3MediaUrlResolver);
    $result = $useCase->execute($user);

    expect($result)->toHaveCount(1);
    expect($result[0])->toHaveKeys([
        'id', 'name', 'year', 'is_official', 'member_count', 'edition_status', 'competition',
    ]);
    expect($result[0]['is_official'])->toBeTrue();
    expect($result[0]['member_count'])->toBe(2);
    expect($result[0]['edition_status'])->toBe('upcoming');
    expect($result[0]['competition'])->toHaveKeys(['name', 'type', 'coverImageUrl', 'logoImageUrl']);
    expect($result[0]['competition']['type'])->toBe('gc');
});

test('league show page exposes the season leagues prop', function () {
    $user = User::factory()->create();

    $currentYear = (int) date('Y');

    $season = createSeasonLeague('Liga Temporada', $currentYear, $currentYear.'-07-01', $user);

    $response = $this->actingAs($user)->get(route('leagues.show', $season['league']->id));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Leagues/Show')
        ->where('league_id', $season['league']->id)
        ->has('season_leagues', 1)
        ->where('season_leagues.0.id', $season['league']->id)
        ->where('season_leagues.0.name', 'Liga Temporada')
    );
});
