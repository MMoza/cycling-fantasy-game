<?php

use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\Admin\Edition\StoreEditionUseCase;
use App\Application\UseCases\League\JoinLeagueUseCase;
use App\Application\UseCases\Season\ShowSeasonClassificationUseCase;
use App\Application\UseCases\Season\ShowSeasonUseCase;
use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('admin creating edition auto-creates official league', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Tour de Francia',
        'type' => CompetitionType::GC,
        'country_id' => createCountry(),
        'active' => true,
    ]);

    ScoringSystemModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Conservador',
        'type' => ScoringSystemType::Conservative,
        'description' => 'Puntuación más repartida',
    ]);

    $useCase = new StoreEditionUseCase;
    $edition = $useCase->execute($competition->id, [
        'year' => 2026,
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-23',
    ], $admin->id);

    $this->assertDatabaseHas('editions', [
        'id' => $edition->id,
        'competition_id' => $competition->id,
        'year' => 2026,
    ]);

    $officialLeague = LeagueModel::where('edition_id', $edition->id)
        ->where('is_official', true)
        ->first();

    expect($officialLeague)->not->toBeNull();
    expect($officialLeague->name)->toBe('Liga Oficial Tour de Francia 2026');
    expect($officialLeague->is_public)->toBeTrue();
    expect($officialLeague->owner_id)->toBe($admin->id);

    $this->assertDatabaseHas('league_user', [
        'league_id' => $officialLeague->id,
        'user_id' => $admin->id,
        'role' => 'owner',
    ]);
});

test('user can join official league by id without invite code', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['plan' => 'free']);

    $competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Tour de Francia',
        'type' => CompetitionType::GC,
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

    $league = LeagueModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Liga Oficial',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'owner_id' => $admin->id,
        'invite_code' => 'TESTCODE',
        'is_official' => true,
        'is_public' => true,
    ]);

    $useCase = new JoinLeagueUseCase;
    $result = $useCase->executeById($user, $league->id);

    expect($result->id)->toBe($league->id);

    $this->assertDatabaseHas('league_user', [
        'league_id' => $league->id,
        'user_id' => $user->id,
        'role' => 'member',
    ]);
});

test('user cannot join non-official league by id', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['plan' => 'premium']);

    $competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Tour de Francia',
        'type' => CompetitionType::GC,
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
        'name' => 'Liga Privada',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'owner_id' => $admin->id,
        'invite_code' => 'PRIVCODE',
        'is_official' => false,
        'is_public' => false,
    ]);

    $useCase = new JoinLeagueUseCase;

    expect(fn () => $useCase->executeById($user, $league->id))
        ->toThrow(ApplicationException::class);
});

test('season use case returns only official leagues for current year', function () {
    $user = User::factory()->create();

    $competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Tour de Francia',
        'type' => CompetitionType::GC,
        'country_id' => createCountry(),
        'active' => true,
    ]);

    $edition = EditionModel::create([
        'id' => Str::uuid()->toString(),
        'competition_id' => $competition->id,
        'year' => (int) date('Y'),
        'start_date' => date('Y').'-07-01',
        'end_date' => date('Y').'-07-23',
        'status' => EditionStatus::Upcoming,
    ]);

    $scoringSystem = ScoringSystemModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Conservador',
        'type' => ScoringSystemType::Conservative,
        'description' => 'Puntuación más repartida',
    ]);

    LeagueModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Liga Oficial',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'owner_id' => $user->id,
        'invite_code' => 'OFFICIAL',
        'is_official' => true,
        'is_public' => true,
    ]);

    LeagueModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Liga Privada',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'owner_id' => $user->id,
        'invite_code' => 'PRIVATE1',
        'is_official' => false,
        'is_public' => false,
    ]);

    $useCase = new ShowSeasonUseCase;
    $result = $useCase->execute($user);

    expect($result['year'])->toBe((int) date('Y'));
    expect($result['competitions'])->toHaveCount(1);
    expect($result['competitions'][0]->competitionName)->toBe('Tour de Francia');
});

test('season classification aggregates scores across official leagues', function () {
    $user1 = User::factory()->create(['name' => 'User 1']);
    $user2 = User::factory()->create(['name' => 'User 2']);

    $competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Tour de Francia',
        'type' => CompetitionType::GC,
        'country_id' => createCountry(),
        'active' => true,
    ]);

    $edition = EditionModel::create([
        'id' => Str::uuid()->toString(),
        'competition_id' => $competition->id,
        'year' => (int) date('Y'),
        'start_date' => date('Y').'-07-01',
        'end_date' => date('Y').'-07-23',
        'status' => EditionStatus::Ongoing,
    ]);

    $scoringSystem = ScoringSystemModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Conservador',
        'type' => ScoringSystemType::Conservative,
        'description' => 'Puntuación más repartida',
    ]);

    $league = LeagueModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Liga Oficial',
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'owner_id' => $user1->id,
        'invite_code' => 'OFFICIAL',
        'is_official' => true,
        'is_public' => true,
    ]);

    $league->users()->attach($user1->id, ['id' => Str::uuid()->toString(), 'role' => 'owner']);
    $league->users()->attach($user2->id, ['id' => Str::uuid()->toString(), 'role' => 'member']);

    ScoreEventModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $user1->id,
        'league_id' => $league->id,
        'scoring_rule_id' => Str::uuid()->toString(),
        'points' => 100,
        'description' => 'Test score',
        'context' => 'test',
    ]);

    ScoreEventModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $user2->id,
        'league_id' => $league->id,
        'scoring_rule_id' => Str::uuid()->toString(),
        'points' => 50,
        'description' => 'Test score',
        'context' => 'test',
    ]);

    $useCase = new ShowSeasonClassificationUseCase;
    $result = $useCase->execute($user1);

    expect($result['aggregated_leaderboard'])->toHaveCount(2);
    expect($result['aggregated_leaderboard'][0]->userName)->toBe('User 1');
    expect($result['aggregated_leaderboard'][0]->totalPoints)->toBe(100);
    expect($result['aggregated_leaderboard'][1]->userName)->toBe('User 2');
    expect($result['aggregated_leaderboard'][1]->totalPoints)->toBe(50);
});

test('season page is accessible for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('season.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Season/Index')
        ->has('year')
        ->has('competitions')
    );
});

test('season classification page is accessible for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('season.classification'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Season/Classification')
        ->has('year')
        ->has('aggregated_leaderboard')
        ->has('per_competition')
    );
});
