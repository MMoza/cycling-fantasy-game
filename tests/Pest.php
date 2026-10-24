<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(TestCase::class)->in('Feature', 'Integration');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use App\Infrastructure\Persistence\Models\CountryModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageParticipantModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Infrastructure\Persistence\Models\TeamRosterModel;
use App\Models\User;
use Illuminate\Support\Str;

function createCountry(string $code = 'FR', string $name = 'Francia'): string
{
    CountryModel::create(['id' => $code, 'name' => $name]);

    return $code;
}

function createTestRider(string $firstName, string $lastName, string $countryId = 'FR'): RiderModel
{
    return RiderModel::firstOrCreate(
        ['first_name' => $firstName, 'last_name' => $lastName],
        ['id' => Str::uuid()->toString(), 'country_id' => $countryId]
    );
}

function createTestTeam(string $name, string $abbr = 'TST', string $countryId = 'FR'): TeamModel
{
    return TeamModel::firstOrCreate(
        ['name' => $name],
        ['id' => Str::uuid()->toString(), 'abbreviation' => $abbr, 'country_id' => $countryId]
    );
}

function createTestParticipant(string $competitionId, string $editionId, string $teamId, string $riderId): CompetitionParticipantModel
{
    return CompetitionParticipantModel::firstOrCreate(
        ['edition_id' => $editionId, 'team_id' => $teamId, 'rider_id' => $riderId],
        ['id' => Str::uuid()->toString(), 'competition_id' => $competitionId]
    );
}

function createTestRoster(string $teamId, string $riderId, int $year = 2026): TeamRosterModel
{
    return TeamRosterModel::firstOrCreate(
        ['team_id' => $teamId, 'rider_id' => $riderId, 'year' => $year],
        ['id' => Str::uuid()->toString()]
    );
}

function createTestStageParticipant(string $stageId, string $riderId, string $teamId): StageParticipantModel
{
    return StageParticipantModel::firstOrCreate(
        ['stage_id' => $stageId, 'rider_id' => $riderId],
        ['id' => Str::uuid()->toString(), 'team_id' => $teamId]
    );
}

/**
 * Creates a competition + edition for the given year with an official league.
 *
 * @return array{league: LeagueModel, edition: EditionModel}
 */
function createSeasonLeague(
    string $leagueName,
    int $year,
    string $startDate,
    ?User $owner = null,
    ?string $competitionName = null,
    EditionStatus $status = EditionStatus::Upcoming,
): array {
    $owner ??= User::factory()->create();

    CountryModel::firstOrCreate(['id' => 'FR'], ['name' => 'Francia']);

    $competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => $competitionName ?? $leagueName,
        'type' => CompetitionType::GC,
        'country_id' => 'FR',
        'active' => true,
    ]);

    $edition = EditionModel::create([
        'id' => Str::uuid()->toString(),
        'competition_id' => $competition->id,
        'year' => $year,
        'start_date' => $startDate,
        'end_date' => $startDate,
        'status' => $status,
    ]);

    $scoringSystem = ScoringSystemModel::firstOrCreate(
        ['name' => 'Conservador'],
        [
            'id' => Str::uuid()->toString(),
            'type' => ScoringSystemType::Conservative,
            'description' => 'Puntuación más repartida',
        ]
    );

    $league = LeagueModel::create([
        'id' => Str::uuid()->toString(),
        'name' => $leagueName,
        'edition_id' => $edition->id,
        'scoring_system_id' => $scoringSystem->id,
        'owner_id' => $owner->id,
        'invite_code' => strtoupper(Str::random(8)),
        'is_official' => true,
        'is_public' => true,
    ]);

    $league->users()->attach($owner->id, ['id' => Str::uuid()->toString(), 'role' => 'owner']);

    return ['league' => $league, 'edition' => $edition];
}

function joinSeasonLeague(LeagueModel $league, User $user): void
{
    $league->users()->attach($user->id, ['id' => Str::uuid()->toString(), 'role' => 'member']);
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeUuid', function () {
    return $this->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/
