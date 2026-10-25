<?php

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CountryModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * Liga de una edición Major (vuelta de una semana) con el sistema indicado.
 */
function createMajorLeagueForScoring(string $leagueName, int $year, ScoringSystemType $systemType): LeagueModel
{
    CountryModel::firstOrCreate(['id' => 'FR'], ['name' => 'Francia']);

    $system = ScoringSystemModel::firstOrCreate(
        ['type' => $systemType->value],
        [
            'id' => Str::uuid()->toString(),
            'name' => ucfirst($systemType->value),
            'description' => 'Sistema '.$systemType->value,
        ],
    );

    $competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => $leagueName,
        'type' => CompetitionType::Major,
        'country_id' => 'FR',
        'active' => true,
    ]);

    $edition = EditionModel::create([
        'id' => Str::uuid()->toString(),
        'competition_id' => $competition->id,
        'year' => $year,
        'start_date' => $year.'-03-07',
        'end_date' => $year.'-03-14',
        'status' => 'upcoming',
    ]);

    $owner = User::factory()->create();

    return LeagueModel::create([
        'id' => Str::uuid()->toString(),
        'name' => $leagueName,
        'edition_id' => $edition->id,
        'scoring_system_id' => $system->id,
        'owner_id' => $owner->id,
        'invite_code' => strtoupper(Str::random(8)),
        'is_official' => true,
        'is_public' => true,
    ]);
}

test('adapt one-week scoring switches standard leagues of the season', function () {
    $league = createMajorLeagueForScoring('Liga Semana', 2027, ScoringSystemType::Standard);

    $this->artisan('season:adapt-one-week-scoring', ['year' => 2027])->assertExitCode(0);

    $league->refresh();
    expect($league->scoringSystem->type)->toBe(ScoringSystemType::OneWeek);
});

test('adapt one-week scoring keeps non-standard systems unless forced', function () {
    $conservative = createMajorLeagueForScoring('Liga Conservadora', 2027, ScoringSystemType::Conservative);

    $this->artisan('season:adapt-one-week-scoring', ['year' => 2027])->assertExitCode(0);

    $conservative->refresh();
    expect($conservative->scoringSystem->type)->toBe(ScoringSystemType::Conservative);

    $this->artisan('season:adapt-one-week-scoring', ['year' => 2027, '--all' => true])->assertExitCode(0);

    $conservative->refresh();
    expect($conservative->scoringSystem->type)->toBe(ScoringSystemType::OneWeek);
});

test('adapt one-week scoring skips leagues already adapted', function () {
    $league = createMajorLeagueForScoring('Liga Ya Adaptada', 2027, ScoringSystemType::OneWeek);

    $this->artisan('season:adapt-one-week-scoring', ['year' => 2027])->assertExitCode(0);

    $league->refresh();
    expect($league->scoringSystem->type)->toBe(ScoringSystemType::OneWeek);
});

test('adapt one-week scoring only touches one-week races of the given year', function () {
    $grandTour = createMajorLeagueForScoring('Gran Vuelta', 2027, ScoringSystemType::Standard);
    $competition = CompetitionModel::where('name', 'Gran Vuelta')->firstOrFail();
    $competition->update(['type' => CompetitionType::GC]);

    $otherYear = createMajorLeagueForScoring('Liga 2026', 2026, ScoringSystemType::Standard);

    $this->artisan('season:adapt-one-week-scoring', ['year' => 2027])->assertExitCode(0);

    $grandTour->refresh();
    $otherYear->refresh();
    expect($grandTour->scoringSystem->type)->toBe(ScoringSystemType::Standard);
    expect($otherYear->scoringSystem->type)->toBe(ScoringSystemType::Standard);
});

test('adapt one-week scoring dry-run reports without changing anything', function () {
    $league = createMajorLeagueForScoring('Liga Dry', 2027, ScoringSystemType::Standard);

    $this->artisan('season:adapt-one-week-scoring', ['year' => 2027, '--dry-run' => true])->assertExitCode(0);

    $league->refresh();
    expect($league->scoringSystem->type)->toBe(ScoringSystemType::Standard);
});

test('adapt one-week scoring fails when the one-week system is missing', function () {
    createMajorLeagueForScoring('Liga Sin Sistema', 2027, ScoringSystemType::Standard);
    ScoringSystemModel::where('type', ScoringSystemType::OneWeek->value)->delete();

    $this->artisan('season:adapt-one-week-scoring', ['year' => 2027])->assertExitCode(1);
});
