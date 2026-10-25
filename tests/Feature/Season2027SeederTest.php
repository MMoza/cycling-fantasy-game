<?php

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CountryModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Models\User;
use Database\Seeders\Season2027Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('season 2027 seeder creates all editions with PCS dates and types', function () {
    $this->seed(Season2027Seeder::class);

    expect(EditionModel::where('year', 2027)->count())->toBe(29);

    $tour = CompetitionModel::where('name', 'Tour de France')->firstOrFail();
    expect($tour->type)->toBe(CompetitionType::GC);
    expect($tour->country_id)->toBe('FR');
    expect($tour->pcs_slug)->toBe('tour-de-france');
    expect($tour->editions()->where('year', 2027)->first()->start_date->toDateString())->toBe('2027-07-02');
    expect($tour->editions()->where('year', 2027)->first()->end_date->toDateString())->toBe('2027-07-25');

    $lombardia = CompetitionModel::where('name', 'Il Lombardia')->firstOrFail();
    expect($lombardia->type)->toBe(CompetitionType::Monument);
    expect($lombardia->pcs_slug)->toBe('il-lombardia');
    expect($lombardia->editions()->where('year', 2027)->first()->start_date->toDateString())->toBe('2027-10-09');

    $e3 = CompetitionModel::where('name', 'E3 Classic')->firstOrFail();
    expect($e3->type)->toBe(CompetitionType::Classic);
    expect($e3->pcs_slug)->toBe('e3-harelbeke');
    expect($e3->editions()->where('year', 2027)->first()->start_date->toDateString())->toBe('2027-03-26');

    $europeans = CompetitionModel::where('name', 'European Championships')->firstOrFail();
    expect($europeans->type)->toBe(CompetitionType::Championship);
    expect($europeans->pcs_slug)->toBe('uec-road-european-championships');

    $auvergne = CompetitionModel::where('name', 'Tour Auvergne - Rhône-Alpes')->firstOrFail();
    expect($auvergne->type)->toBe(CompetitionType::Major);
    expect($auvergne->editions()->where('year', 2027)->first()->end_date->toDateString())->toBe('2027-06-13');

    // Slugs verificados de PCS (incluidos los nada obvios)
    expect(CompetitionModel::where('pcs_slug', 'giro-d-italia')->exists())->toBeTrue();
    expect(CompetitionModel::where('pcs_slug', 'world-championship')->exists())->toBeTrue();
    expect(CompetitionModel::where('pcs_slug', 'la-fleche-wallone')->exists())->toBeTrue();
    expect(CompetitionModel::where('pcs_slug', 'gent-wevelgem')->exists())->toBeTrue();
});

test('season 2027 seeder creates an official league per edition with scoring by race type', function () {
    $this->seed(Season2027Seeder::class);

    $officialLeagues = LeagueModel::where('is_official', true)
        ->whereHas('edition', fn ($q) => $q->where('year', 2027))
        ->get();

    expect($officialLeagues)->toHaveCount(29);

    foreach ($officialLeagues as $league) {
        expect($league->is_public)->toBeTrue();
        expect($league->name)->toBe("Liga Oficial {$league->edition->competition->name} 2027");
    }

    $oneDayType = ScoringSystemModel::where('type', ScoringSystemType::OneDay->value)->firstOrFail()->id;
    $standardType = ScoringSystemModel::where('type', ScoringSystemType::Standard->value)->firstOrFail()->id;

    $tourLeague = LeagueModel::where('name', 'Liga Oficial Tour de France 2027')->firstOrFail();
    expect($tourLeague->scoring_system_id)->toBe($standardType);

    $lombardiaLeague = LeagueModel::where('name', 'Liga Oficial Il Lombardia 2027')->firstOrFail();
    expect($lombardiaLeague->scoring_system_id)->toBe($oneDayType);

    $e3League = LeagueModel::where('name', 'Liga Oficial E3 Classic 2027')->firstOrFail();
    expect($e3League->scoring_system_id)->toBe($oneDayType);

    $admin = User::where('is_admin', true)->firstOrFail();
    expect($tourLeague->owner_id)->toBe($admin->id);
});

test('season 2027 seeder normalizes existing spanish-named competitions without duplicating them', function () {
    CountryModel::firstOrCreate(['id' => 'FR'], ['name' => 'Francia']);

    $existing = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Tour de Francia',
        'type' => CompetitionType::GC,
        'country_id' => 'FR',
        'active' => true,
    ]);

    EditionModel::create([
        'id' => Str::uuid()->toString(),
        'competition_id' => $existing->id,
        'year' => 2026,
        'start_date' => '2026-07-04',
        'end_date' => '2026-07-26',
        'status' => 'finished',
    ]);

    $this->seed(Season2027Seeder::class);

    expect(CompetitionModel::where('pcs_slug', 'tour-de-france')->count())->toBe(1);
    expect(CompetitionModel::where('name', 'Tour de Francia')->exists())->toBeFalse();

    $existing->refresh();
    expect($existing->name)->toBe('Tour de France');
    expect($existing->pcs_slug)->toBe('tour-de-france');

    // La edición 2026 se conserva y la 2027 cuelga de la MISMA competición
    expect($existing->editions()->pluck('year')->sort()->values()->all())->toBe([2026, 2027]);
});

test('season 2027 seeder is idempotent', function () {
    $this->seed(Season2027Seeder::class);
    $this->seed(Season2027Seeder::class);

    expect(EditionModel::where('year', 2027)->count())->toBe(29);
    expect(CompetitionModel::where('pcs_slug', 'tour-de-france')->count())->toBe(1);
    expect(LeagueModel::where('is_official', true)->whereHas('edition', fn ($q) => $q->where('year', 2027))->count())->toBe(29);
});
