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

use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use App\Infrastructure\Persistence\Models\CountryModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Infrastructure\Persistence\Models\TeamRosterModel;
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
